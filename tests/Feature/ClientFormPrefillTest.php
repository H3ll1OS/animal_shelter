<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientFormPrefillTest extends TestCase
{
    use RefreshDatabase;

    public function test_adoption_page_uses_shared_profile_data_without_exposing_the_full_user_model(): void
    {
        $user = User::factory()->create([
            'name' => 'Jamie Sample',
            'email' => 'jamie@example.com',
            'phone' => '09171234567',
            'address' => 'Surigao City',
        ]);
        $pet = Pet::create([
            'name' => 'Milo',
            'species' => 'Dog',
            'size' => 'small',
            'gender' => 'Male',
            'status' => 'Available',
        ]);

        $this->actingAs($user)
            ->get(route('pets.show', $pet))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Frontend/Pet')
                ->where('auth.user.name', 'Jamie Sample')
                ->where('auth.user.email', 'jamie@example.com')
                ->where('auth.user.phone', '09171234567')
                ->where('auth.user.address', 'Surigao City')
                ->missing('user'));
    }

    public function test_adoption_submission_saves_edited_details_but_uses_authenticated_user(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'name' => 'Jamie Sample',
            'email' => 'jamie@example.com',
            'phone' => '09171234567',
        ]);
        $otherUser = User::factory()->create();
        $pet = Pet::create([
            'name' => 'Milo',
            'species' => 'Dog',
            'size' => 'small',
            'gender' => 'Male',
            'status' => 'Available',
        ]);

        $this->actingAs($user)->post(route('pets.adopt', $pet), [
            'user_id' => $otherUser->id,
            'full_name' => 'Jamie Updated',
            'email' => 'updated@example.com',
            'phone' => '09998887777',
            'message' => 'I have a secure yard.',
            'documents' => [UploadedFile::fake()->image('proof.jpg')],
        ])->assertRedirect();

        $this->assertDatabaseHas('adoptions', [
            'user_id' => $user->id,
            'full_name' => 'Jamie Updated',
            'email' => 'updated@example.com',
            'phone' => '09998887777',
        ]);
        $this->assertDatabaseMissing('adoptions', ['user_id' => $otherUser->id]);
        $this->assertSame('Jamie Sample', $user->fresh()->name);
        $this->assertSame('jamie@example.com', $user->fresh()->email);
        $this->assertSame('09171234567', $user->fresh()->phone);
    }

    public function test_donation_submission_uses_authenticated_user_and_keeps_profile_unchanged(): void
    {
        Storage::fake('local');
        $user = User::factory()->create([
            'name' => 'Jamie Sample',
            'email' => 'jamie@example.com',
        ]);
        $otherUser = User::factory()->create();

        $this->actingAs($user)->post(route('donations.store'), [
            'user_id' => $otherUser->id,
            'name' => 'Jamie Updated',
            'last_name' => 'Donor',
            'email' => 'updated@example.com',
            'amount' => 100,
            'payment_method' => 'gcash',
            'gcash_number' => '09171234567',
            'receipt' => UploadedFile::fake()->image('payment-proof.png'),
        ])->assertRedirect(route('donate'));

        $this->assertDatabaseHas('donations', [
            'user_id' => $user->id,
            'name' => 'Jamie Updated',
            'last_name' => 'Donor',
            'email' => 'updated@example.com',
        ]);
        $this->assertDatabaseMissing('donations', ['user_id' => $otherUser->id]);
        $this->assertSame('Jamie Sample', $user->fresh()->name);
        $this->assertSame('jamie@example.com', $user->fresh()->email);
    }
}
