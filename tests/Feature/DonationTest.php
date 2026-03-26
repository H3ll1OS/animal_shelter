<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ];

        $response = $this->actingAs($user)->post(route('donate.store'), $donationData);

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
}
