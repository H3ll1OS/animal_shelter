<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            'gcash_number' => '09171234567',
            'receipt' => UploadedFile::fake()->image('payment-proof.png'),
        ];

        $response = $this->post(route('donations.store'), $donationData);

        $response->assertRedirect(route('donate'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('donations', [
            'name' => 'John Doe',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'amount' => 100.00,
            'payment_method' => 'gcash',
            'user_id' => null,
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

        $second = Donation::create($this->donationData([
            'status' => 'Pending',
        ]));
        $this->actingAs($admin)
            ->post(route('admin.donations.reject', $second), ['rejection_reason' => 'Invalid proof'])
            ->assertRedirect();

        $this->assertSame('Rejected', $second->fresh()->status);
        $this->assertSame('Invalid proof', $second->fresh()->rejection_reason);
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
