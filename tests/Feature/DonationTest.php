<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use App\Notifications\DonationReviewedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_make_donation()
    {
        $donationData = [
            'name' => 'John Doe',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'state' => 'CA',
            'country' => 'USA',
            'zip_code' => '12345',
            'amount' => 100.00,
            'payment_method' => 'gcash',
            'status' => 'Verified',
            'verified_by' => 99999,
            'gcash_number' => '09171234567',
            'receipt' => UploadedFile::fake()->image('payment-proof.png'),
        ];

        $response = $this->post(route('donations.store'), $donationData);

        $response->assertRedirect(route('donate'));
        $response->assertSessionHas(
            'success',
            'Your donation details and payment receipt have been successfully submitted. Our shelter staff will review your submission and verify your payment. You may be contacted if additional confirmation or information is needed.'
        );

        $this->assertDatabaseHas('donations', [
            'name' => 'John Doe',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'amount' => 100.00,
            'payment_method' => 'gcash',
            'user_id' => null,
            'status' => 'Pending',
        ]);
    }

    public function test_authenticated_user_can_make_donation()
    {
        $user = User::factory()->create();

        $donationData = [
            'name' => 'Jane Doe',
            'last_name' => 'Johnson',
            'email' => 'jane@example.com',
            'state' => 'NY',
            'country' => 'USA',
            'zip_code' => '67890',
            'amount' => 50.00,
            'payment_method' => 'paypal',
            'paypal_email' => 'jane@example.com',
            'receipt' => UploadedFile::fake()->image('payment-proof.png'),
        ];

        $response = $this->actingAs($user)->post(route('donations.store'), $donationData);

        $response->assertRedirect(route('donate'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('donations', [
            'name' => 'Jane Doe',
            'last_name' => 'Johnson',
            'email' => 'jane@example.com',
            'amount' => 50.00,
            'payment_method' => 'paypal',
            'user_id' => $user->id,
        ]);
    }

    public function test_mobile_donation_confirmation_explains_staff_review_and_verification(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->issueApiToken())
            ->postJson('/api/mobile/donations', [
                'amount' => 50,
                'payment_method' => 'gcash',
            ])
            ->assertCreated()
            ->assertJsonPath(
                'message',
                'Donation details submitted successfully. Our shelter staff will review your submission and verify your payment. You may be contacted if additional confirmation or information is needed. Your donation will only be considered verified after it has been reviewed and confirmed by our staff.'
            )
            ->assertJsonPath('donation.status', 'Pending');
    }

    public function test_donor_can_upload_a_private_payment_receipt_with_a_donation(): void
    {
        Storage::fake('local');

        $this->post(route('donations.store'), $this->donationData([
            'receipt' => UploadedFile::fake()->image('payment-proof.png'),
        ]))->assertRedirect(route('donate'));

        $donation = Donation::query()->firstOrFail();
        $this->assertNotNull($donation->receipt_path);
        $this->assertStringStartsWith('donation-receipts/', $donation->receipt_path);
        Storage::disk('local')->assertExists($donation->receipt_path);
        $this->assertTrue($donation->has_receipt);
    }

    public function test_donation_receipt_is_required_for_new_donation_workflow(): void
    {
        $this->post(route('donations.store'), $this->donationData())
            ->assertSessionHasInput('amount', 50)
            ->assertSessionHasErrors('receipt');

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_receipt_rejects_non_image_and_oversized_uploads(): void
    {
        $this->from(route('donate'))->post(route('donations.store'), $this->donationData([
            'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('donate'))->assertSessionHasErrors('receipt');

        $this->from(route('donate'))->post(route('donations.store'), $this->donationData([
            'receipt' => UploadedFile::fake()->image('large.png')->size(5121),
        ]))->assertRedirect(route('donate'))->assertSessionHasErrors('receipt');

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_only_admins_can_open_a_donation_receipt(): void
    {
        Storage::fake('local');
        $receiptPath = UploadedFile::fake()->image('payment-proof.jpg')->store('donation-receipts', 'local');
        $donation = Donation::create($this->donationData([
            'receipt_path' => $receiptPath,
        ]));

        $this->get(route('admin.donations.receipt', $donation))->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'user', 'status' => 'Active']);
        $this->actingAs($user)->get(route('admin.donations.receipt', $donation))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $response = $this->actingAs($admin)->get(route('admin.donations.receipt', $donation));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_older_donations_without_a_receipt_remain_viewable(): void
    {
        $donation = Donation::create($this->donationData());
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)->get(route('admin.donations.show', $donation))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Donations/Show')
                ->where('donation.has_receipt', false)
                ->missing('donation.receipt_path'));

        $this->actingAs($admin)->get(route('admin.donations.receipt', $donation))->assertNotFound();
    }

    public function test_admin_can_verify_or_reject_a_pending_donation(): void
    {
        config(['mail.default' => 'smtp']);
        Notification::fake();
        Storage::fake('local');
        $donation = Donation::create($this->donationData([
            'receipt_path' => UploadedFile::fake()->image('receipt.jpg')->store('donation-receipts', 'local'),
            'status' => 'Pending',
        ]));
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)
            ->post(route('admin.donations.verify', $donation))
            ->assertRedirect();

        $this->assertSame('Verified', $donation->fresh()->status);
        $this->assertNotNull($donation->fresh()->verified_at);
        $this->assertSame($admin->id, $donation->fresh()->verified_by);
        $this->assertNull($donation->fresh()->rejected_by);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'donation_verified',
            'module' => 'donations',
        ]);
        $verificationLog = \App\Models\ActivityLog::where('action', 'donation_verified')->firstOrFail();
        $this->assertSame($admin->id, $verificationLog->actor_id);
        $this->assertSame($donation->id, $verificationLog->details['donation_id']);
        $this->assertSame('Pending', $verificationLog->details['previous_status']);
        $this->assertSame('Verified', $verificationLog->details['new_status']);

        $second = Donation::create($this->donationData([
            'status' => 'Pending',
        ]));
        $this->actingAs($admin)
            ->from(route('admin.donations.show', $second))
            ->post(route('admin.donations.reject', $second), [])
            ->assertSessionHasErrors('rejection_reason');

        $this->actingAs($admin)
            ->post(route('admin.donations.reject', $second), ['rejection_reason' => 'Invalid proof'])
            ->assertRedirect();

        $this->assertSame('Rejected', $second->fresh()->status);
        $this->assertSame('Invalid proof', $second->fresh()->rejection_reason);
        $this->assertSame($admin->id, $second->fresh()->rejected_by);
        $this->assertNull($second->fresh()->verified_by);
        $this->assertNotNull($second->fresh()->rejected_at);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'donation_rejected',
            'module' => 'donations',
        ]);
        $rejectionLog = \App\Models\ActivityLog::where('action', 'donation_rejected')->firstOrFail();
        $this->assertSame($admin->id, $rejectionLog->actor_id);
        $this->assertSame($second->id, $rejectionLog->details['donation_id']);
        $this->assertSame('Invalid proof', $rejectionLog->details['rejection_reason']);

        $this->actingAs($admin)
            ->from(route('admin.donations.show', $second))
            ->post(route('admin.donations.verify', $second))
            ->assertSessionHas('error', 'Only pending donations can be verified.');

        $this->assertSame('Rejected', $second->fresh()->status);
        $this->assertSame(2, \App\Models\ActivityLog::where('module', 'donations')->count());
        Notification::assertSentOnDemandTimes(DonationReviewedNotification::class, 2);
        Notification::assertSentOnDemand(
            DonationReviewedNotification::class,
            function ($notification, $channels, $notifiable) {
                $message = $notification->toMail($notifiable);

                return $notification->status === 'Verified'
                    && $channels === ['mail']
                    && $notifiable->routeNotificationFor('mail') === 'donor@example.com'
                    && $message->subject === 'Donation Verified!'
                    && str_contains(implode(' ', $message->introLines), 'verified by our shelter staff');
            }
        );
        Notification::assertSentOnDemand(
            DonationReviewedNotification::class,
            function ($notification, $channels, $notifiable) {
                $message = $notification->toMail($notifiable);

                return $notification->status === 'Rejected'
                    && $notification->rejectionReason === 'Invalid proof'
                    && $channels === ['mail']
                    && $notifiable->routeNotificationFor('mail') === 'donor@example.com'
                    && $message->subject === 'Donation Requires Attention'
                    && str_contains(implode(' ', $message->introLines), 'Reason: Invalid proof');
            }
        );
    }

    public function test_anonymous_donation_does_not_send_a_review_notification(): void
    {
        Notification::fake();
        $donation = Donation::create($this->donationData([
            'anonymous' => true,
            'name' => 'Anonymous',
            'last_name' => 'Donor',
            'email' => 'anonymous@donor.local',
            'status' => 'Pending',
        ]));
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)->post(route('admin.donations.verify', $donation))->assertRedirect();

        Notification::assertNothingSent();
    }

    public function test_notification_failure_does_not_undo_successful_verification(): void
    {
        $donation = Donation::create($this->donationData(['status' => 'Pending']));
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        config(['mail.default' => 'smtp']);
        $this->mock(Dispatcher::class)
            ->shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('Mail transport unavailable.'));

        $this->actingAs($admin)
            ->post(route('admin.donations.verify', $donation))
            ->assertRedirect()
            ->assertSessionHas('success', 'Donation marked as verified.')
            ->assertSessionHas('warning');

        $this->assertSame('Verified', $donation->fresh()->status);
    }

    public function test_unconfigured_mailer_is_reported_without_claiming_a_donor_notification_was_sent(): void
    {
        config(['mail.default' => 'log']);
        Notification::fake();
        $donation = Donation::create($this->donationData(['status' => 'Pending']));
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)
            ->post(route('admin.donations.verify', $donation))
            ->assertRedirect()
            ->assertSessionHas('success', 'Donation marked as verified.')
            ->assertSessionHas('warning', 'Donor email notification was not sent. Configure Laravel mail delivery to enable it.');

        $this->assertSame('Verified', $donation->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_admin_donation_list_filters_and_summarizes_database_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        Donation::create($this->donationData([
            'name' => 'Pending',
            'status' => 'Pending',
            'created_at' => '2026-11-12 10:00:00',
        ]));
        Donation::create($this->donationData([
            'name' => 'Verified',
            'email' => 'verified@example.com',
            'amount' => 200,
            'status' => 'Verified',
            'verified_at' => '2026-11-13 10:00:00',
            'created_at' => '2026-11-13 10:00:00',
        ]));
        Donation::create($this->donationData([
            'name' => 'Rejected',
            'status' => 'Rejected',
            'created_at' => '2026-11-14 10:00:00',
        ]));

        $this->actingAs($admin)
            ->get(route('admin.donations.index', [
                'status' => 'Verified',
                'search' => 'Verified Example',
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'sort_by' => 'amount',
                'sort_direction' => 'asc',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Donations/Index')
                ->where('donations.total', 1)
                ->where('donations.data.0.name', 'Verified')
                ->where('summary.pending', 1)
                ->where('summary.verified', 1)
                ->where('summary.rejected', 1)
                ->where('summary.verified_amount', 200));
    }

    public function test_reviewed_donations_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $donation = Donation::create($this->donationData(['status' => 'Verified']));

        $this->actingAs($admin)
            ->delete(route('admin.donations.destroy', $donation))
            ->assertForbidden();

        $this->assertDatabaseHas('donations', ['id' => $donation->id]);
    }

    public function test_regular_users_cannot_verify_or_reject_donations(): void
    {
        $donation = Donation::create($this->donationData(['status' => 'Pending']));
        $user = User::factory()->create(['role' => 'user', 'status' => 'Active']);

        $this->actingAs($user)->post(route('admin.donations.verify', $donation))->assertForbidden();
        $this->actingAs($user)->post(route('admin.donations.reject', $donation))->assertForbidden();
    }

    private function donationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Donor',
            'last_name' => 'Example',
            'email' => 'donor@example.com',
            'amount' => 50,
            'payment_method' => 'gcash',
            'gcash_number' => '09171234567',
        ], $overrides);
    }
}
