<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_blocked_admin_token_cannot_load_privileged_mobile_bootstrap_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $token = $admin->issueApiToken();
        $admin->update(['status' => 'Blocked']);
        Pet::create([
            'name' => 'Adopted Pet',
            'species' => 'Dog',
            'size' => 'small',
            'gender' => 'Male',
            'status' => 'Adopted',
        ]);
        Donation::create([
            'name' => 'Donor',
            'last_name' => 'Example',
            'email' => 'donor@example.test',
            'amount' => 50,
            'payment_method' => 'gcash',
            'status' => 'Pending',
        ]);

        $this->withToken($token)->getJson('/api/mobile/bootstrap')
            ->assertOk()
            ->assertJsonPath('user', null)
            ->assertJsonCount(0, 'adoptions')
            ->assertJsonCount(0, 'donations')
            ->assertJsonCount(0, 'health_records')
            ->assertJsonCount(0, 'vaccine_schedules')
            ->assertJsonCount(0, 'euthanasia_logs')
            ->assertJsonCount(0, 'pets');
    }

    public function test_mobile_password_reset_response_does_not_disclose_account_existence(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $existing = $this->postJson('/api/mobile/forgot-password', [
            'email' => 'existing@example.test',
        ])->assertOk()->json('message');
        $missing = $this->postJson('/api/mobile/forgot-password', [
            'email' => 'missing@example.test',
        ])->assertOk()->json('message');

        $this->assertSame($existing, $missing);
    }
}
