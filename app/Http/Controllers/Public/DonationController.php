<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DonationController extends Controller
{
    public function store(Request $request)
    {
        $isAnonymous = $request->boolean('anonymous');

        $validated = $request->validate([
            'name' => 'exclude_if:anonymous,true|required|string|max:255',
            'last_name' => 'exclude_if:anonymous,true|required|string|max:255',
            'email' => 'exclude_if:anonymous,true|required|email|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:1.00|max:99999999.99',
            'payment_method' => 'required|string|in:gcash,paypal,bank_transfer,credit_card',
            'anonymous' => 'nullable|boolean',
            'gcash_number' => 'required_if:payment_method,gcash|nullable|regex:/^\d{10,15}$/',
            'gcash_ref' => 'nullable|regex:/^\d{6,30}$/',
            'paypal_email' => 'required_if:payment_method,paypal|nullable|email|max:255',
            'bank_name' => 'required_if:payment_method,bank_transfer|nullable|string|max:255',
            'bank_ref' => 'required_if:payment_method,bank_transfer|nullable|regex:/^\d{6,30}$/',
            'card_name' => 'required_if:payment_method,credit_card|nullable|string|max:255',
            'card_last4' => 'required_if:payment_method,credit_card|nullable|regex:/^\d{4}$/',
            'receipt' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:5120'],
        ]);

        $donationData = $validated;
        unset($donationData['receipt']);
        $donationData['anonymous'] = $isAnonymous;
        $donationData['status'] = 'Pending';

        // Keep DB non-null constraints satisfied for anonymous donations.
        if ($isAnonymous) {
            $donationData['name'] = 'Anonymous';
            $donationData['last_name'] = 'Donor';
            $donationData['email'] = 'anonymous@donor.local';
        }

        if (Auth::check()) {
            $donationData['user_id'] = Auth::id();
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store(
                'donation-receipts',
                config('filesystems.donation_receipts_disk', 'local')
            );

            if (! is_string($receiptPath) || $receiptPath === '') {
                return back()->withInput()->withErrors([
                    'receipt' => 'The receipt could not be uploaded. Please try again.',
                ]);
            }

            $donationData['receipt_path'] = $receiptPath;
        }

        // Build a notes summary of payment details.
        $details = [];
        $method = $validated['payment_method'] ?? null;
        if ($method) {
            $details[] = 'Method: ' . str_replace('_', ' ', ucfirst($method));
            $detailMap = [
                'gcash' => ['gcash_number' => 'GCash Number', 'gcash_ref' => 'Ref'],
                'paypal' => ['paypal_email' => 'PayPal Email'],
                'bank_transfer' => ['bank_name' => 'Bank', 'bank_ref' => 'Transfer Ref'],
                'credit_card' => ['card_name' => 'Card Name', 'card_last4' => 'Last 4'],
            ];
            foreach ($detailMap[$method] ?? [] as $field => $label) {
                if (!empty($validated[$field])) {
                    $details[] = $label . ': ' . $validated[$field];
                }
            }
        }

        $donationData['notes'] = implode(' | ', $details);

        try {
            $donation = DB::transaction(fn () => Donation::create($donationData));
        } catch (QueryException $exception) {
            if ($receiptPath && ! Storage::disk(config('filesystems.donation_receipts_disk', 'local'))->delete($receiptPath)) {
                report(new \RuntimeException('Failed to remove an uploaded donation receipt after the donation could not be saved.'));
            }

            report($exception);

            return back()->withInput()->withErrors([
                'receipt' => 'The donation could not be saved. Please try again.',
            ]);
        }

        $donorLabel = $isAnonymous ? 'Anonymous Donor' : $donation->name;

        return redirect()
            ->route('donate')
            ->with('success', 'Thank you, ' . $donorLabel . '! Your donation of PHP ' . number_format($donation->amount, 2) . ' has been successfully processed.');
    }
}
