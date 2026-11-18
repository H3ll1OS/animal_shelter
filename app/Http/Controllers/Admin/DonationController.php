<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Donation;
use App\Notifications\DonationReviewedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Throwable;

class DonationController extends Controller
{
    /**
     * Display a listing of donations for the admin panel.
     */
    public function index(Request $request)
    {
        Donation::query()
            ->whereNull('admin_seen_at')
            ->update(['admin_seen_at' => now()]);

        $filters = $request->validate([
            'status' => ['nullable', 'in:Pending,Verified,Rejected'],
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'sort_by' => ['nullable', 'in:created_at,amount,status'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
        ]);
        $filters = array_merge([
            'status' => '',
            'search' => '',
            'start_date' => '',
            'end_date' => '',
            'sort_by' => 'created_at',
            'sort_direction' => 'desc',
        ], $filters);
        foreach (['status', 'search', 'start_date', 'end_date'] as $key) {
            $filters[$key] ??= '';
        }
        $filters['sort_by'] = $filters['sort_by'] ?: 'created_at';
        $filters['sort_direction'] = $filters['sort_direction'] ?: 'desc';

        $query = Donation::query();
        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if ($filters['search'] !== '') {
            $terms = preg_split('/\s+/', trim($filters['search'])) ?: [];
            foreach ($terms as $term) {
                $query->where(function ($donations) use ($term) {
                    $donations->where('name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%')
                        ->orWhere('payment_method', 'like', '%'.$term.'%');
                });
            }
        }
        if ($filters['start_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if ($filters['end_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $donations = $query
            ->orderBy($filters['sort_by'], $filters['sort_direction'])
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Donations/Index', [
            'donations' => $donations,
            'filters' => $filters,
            'summary' => [
                'pending' => Donation::where('status', 'Pending')->count(),
                'verified' => Donation::where('status', 'Verified')->count(),
                'rejected' => Donation::where('status', 'Rejected')->count(),
                'verified_amount' => (float) Donation::where('status', 'Verified')->sum('amount'),
            ],
        ]);
    }

    /**
     * Display the specified donation record.
     */
    public function show(Donation $donation)
    {
        if ($donation->admin_seen_at === null) {
            $donation->update(['admin_seen_at' => now()]);
        }

        $donation->load('user:id,name,email', 'verifier:id,name', 'rejecter:id,name');

        return Inertia::render('Admin/Donations/Show', [
            'donation' => $donation,
        ]);
    }

    public function receipt(Donation $donation)
    {
        abort_unless($donation->receipt_path, 404);

        $disk = Storage::disk(config('filesystems.donation_receipts_disk', 'local'));
        abort_unless($disk->exists($donation->receipt_path), 404);

        $mimeType = $disk->mimeType($donation->receipt_path);
        abort_unless(in_array($mimeType, ['image/jpeg', 'image/png'], true), 404);

        $extension = $mimeType === 'image/png' ? 'png' : 'jpg';

        return $disk->response(
            $donation->receipt_path,
            "donation-{$donation->id}-receipt.{$extension}",
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
            'inline'
        );
    }

    /**
     * Remove the specified donation from storage.
     */
    public function destroy(Donation $donation)
    {
        abort_unless($donation->status === 'Pending', 403, 'Reviewed donation records cannot be deleted.');

        $donation->delete();

        return redirect()->route('admin.donations.index')
            ->with('error', 'Donation record deleted successfully.');
    }

    public function verify(Request $request, Donation $donation)
    {
        $reviewed = DB::transaction(function () use ($request, $donation) {
            $lockedDonation = Donation::query()->lockForUpdate()->findOrFail($donation->id);
            if (($lockedDonation->status ?: 'Pending') !== 'Pending') {
                return false;
            }

            $previousStatus = $lockedDonation->status ?: 'Pending';
            $lockedDonation->update([
                'status' => 'Verified',
                'rejection_reason' => null,
                'verified_by' => $request->user()->id,
                'rejected_by' => null,
                'verified_at' => now(),
                'rejected_at' => null,
            ]);
            $this->recordReview($request, $lockedDonation, $previousStatus, 'Verified');

            return true;
        });

        if (! $reviewed) {
            return back()->with('error', 'Only pending donations can be verified.');
        }

        $donation->loadMissing('user');
        $notificationSent = $this->notifyDonor($donation, 'Verified');

        $response = back()->with('success', 'Donation marked as verified.');
        if ($notificationSent === false) {
            $response->with('warning', 'Donor email notification was not sent. Configure Laravel mail delivery to enable it.');
        }

        return $response;
    }

    public function reject(Request $request, Donation $donation)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $reviewed = DB::transaction(function () use ($request, $donation, $validated) {
            $lockedDonation = Donation::query()->lockForUpdate()->findOrFail($donation->id);
            if (($lockedDonation->status ?: 'Pending') !== 'Pending') {
                return false;
            }

            $previousStatus = $lockedDonation->status ?: 'Pending';
            $lockedDonation->update([
                'status' => 'Rejected',
                'rejection_reason' => trim($validated['rejection_reason']),
                'verified_by' => null,
                'rejected_by' => $request->user()->id,
                'verified_at' => null,
                'rejected_at' => now(),
            ]);
            $this->recordReview($request, $lockedDonation, $previousStatus, 'Rejected', trim($validated['rejection_reason']));

            return true;
        });

        if (! $reviewed) {
            return back()->with('error', 'Only pending donations can be rejected.');
        }

        $donation->loadMissing('user');
        $notificationSent = $this->notifyDonor($donation, 'Rejected', trim($validated['rejection_reason']));

        $response = back()->with('success', 'Donation marked as rejected.');
        if ($notificationSent === false) {
            $response->with('warning', 'Donor email notification was not sent. Configure Laravel mail delivery to enable it.');
        }

        return $response;
    }

    private function notifyDonor(Donation $donation, string $status, ?string $rejectionReason = null): ?bool
    {
        if ($donation->anonymous) {
            return null;
        }

        $email = $donation->user?->email ?: $donation->email;
        if (! filled($email) || in_array(config('mail.default'), ['log', 'array'], true)) {
            return false;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new DonationReviewedNotification($status, $rejectionReason));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function recordReview(
        Request $request,
        Donation $donation,
        string $previousStatus,
        string $newStatus,
        ?string $rejectionReason = null
    ): void {
        $actor = $request->user();
        ActivityLog::create([
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_email' => $actor->email,
            'action' => $newStatus === 'Verified' ? 'donation_verified' : 'donation_rejected',
            'module' => 'donations',
            'subject_user_id' => $donation->user_id,
            'subject_name' => $donation->anonymous ? 'Anonymous donor' : $donation->full_name,
            'subject_email' => $donation->anonymous ? null : $donation->email,
            'details' => array_filter([
                'donation_id' => $donation->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'rejection_reason' => $rejectionReason,
            ], static fn ($value) => $value !== null),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
