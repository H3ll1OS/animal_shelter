<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class DonationController extends Controller
{
    /**
     * Display a listing of donations for the admin panel.
     */
    public function index()
    {
        Donation::query()
            ->whereNull('admin_seen_at')
            ->update(['admin_seen_at' => now()]);

        $donations = Donation::latest()->paginate(20);
        return Inertia::render('Admin/Donations/Index', [
            'donations' => $donations,
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

        $donation->load('user:id,name,email', 'verifier:id,name');

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
        $donation->delete();
        
        return redirect()->route('admin.donations.index')
                         ->with('error', 'Donation record deleted successfully.');
    }

    public function verify(Request $request, Donation $donation)
    {
        if ($donation->status !== 'Pending') {
            return back()->with('error', 'Only pending donations can be verified.');
        }

        $donation->update([
            'status' => 'Verified',
            'rejection_reason' => null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejected_at' => null,
        ]);

        return back()->with('success', 'Donation marked as verified.');
    }

    public function reject(Request $request, Donation $donation)
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($donation->status !== 'Pending') {
            return back()->with('error', 'Only pending donations can be rejected.');
        }

        $donation->update([
            'status' => 'Rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'verified_by' => $request->user()->id,
            'verified_at' => null,
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Donation marked as rejected.');
    }
}
