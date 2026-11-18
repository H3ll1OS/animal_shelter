<?php

namespace Tests\Feature;

use App\Models\EuthanasiaLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EuthanasiaBulkTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_a_single_eligible_pet(): void
    {
        $admin = $this->admin();
        $pet = $this->pet('Single Pet');

        $this->actingAs($admin)->post('/admin/health/euthanasia', $this->recordData($pet->id))
            ->assertRedirect('/admin/health/euthanasia')
            ->assertSessionHas('success', 'Euthanasia record added.');

        $this->assertDatabaseHas('euthanasia_logs', [
            'pet_id' => $pet->id,
            'user_id' => $admin->id,
            'reason' => 'Severe illness',
        ]);
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'status' => 'Euthanized']);

        $this->actingAs($admin)->from('/admin/health/euthanasia/create')
            ->post('/admin/health/euthanasia', $this->recordData($pet->id))
            ->assertSessionHasErrors('pet_ids');
        $this->assertDatabaseCount('euthanasia_logs', 1);
    }

    public function test_admin_can_bulk_record_each_selected_pet_with_shared_details(): void
    {
        $admin = $this->admin();
        $pets = [
            $this->pet('Bulk Pet One'),
            $this->pet('Bulk Pet Two'),
            $this->pet('Bulk Pet Three'),
        ];

        $this->actingAs($admin)->post('/admin/health/euthanasia/bulk', [
            'pet_ids' => array_map(fn (Pet $pet) => $pet->id, $pets),
            ...$this->recordData(),
            'confirmed' => true,
        ])->assertRedirect('/admin/health/euthanasia')
            ->assertSessionHas('success', 'Euthanasia records added for 3 pets.');

        $this->assertDatabaseCount('euthanasia_logs', 3);
        foreach ($pets as $pet) {
            $this->assertDatabaseHas('euthanasia_logs', [
                'pet_id' => $pet->id,
                'user_id' => $admin->id,
                'reason' => 'Severe illness',
                'veterinarian' => 'Dr. Shelter',
                'notes' => 'Shared case notes',
            ]);
            $this->assertDatabaseHas('pets', ['id' => $pet->id, 'status' => 'Euthanized']);
        }
    }

    public function test_bulk_recording_requires_confirmation_and_rejects_invalid_ids(): void
    {
        $admin = $this->admin();
        $pet = $this->pet('Eligible Pet');

        $this->actingAs($admin)->post('/admin/health/euthanasia/bulk', [
            'pet_ids' => [$pet->id],
            ...$this->recordData(),
        ])->assertSessionHasErrors('confirmed');

        $this->actingAs($admin)->post('/admin/health/euthanasia/bulk', [
            'pet_ids' => [999999],
            ...$this->recordData(),
            'confirmed' => true,
        ])->assertSessionHasErrors('pet_ids.0');

        $this->assertDatabaseCount('euthanasia_logs', 0);
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'status' => 'Available']);
    }

    public function test_bulk_recording_rolls_back_when_any_pet_is_no_longer_eligible(): void
    {
        $admin = $this->admin();
        $eligiblePet = $this->pet('Still Eligible');
        $previouslyRecordedPet = $this->pet('Previously Recorded', 'Euthanized');
        $this->logFor($admin, $previouslyRecordedPet);

        $this->actingAs($admin)->post('/admin/health/euthanasia/bulk', [
            'pet_ids' => [$eligiblePet->id, $previouslyRecordedPet->id],
            ...$this->recordData(),
            'confirmed' => true,
        ])->assertSessionHasErrors('pet_ids');

        $this->assertDatabaseCount('euthanasia_logs', 1);
        $this->assertDatabaseHas('pets', ['id' => $eligiblePet->id, 'status' => 'Available']);
        $this->assertSame(1, EuthanasiaLog::where('pet_id', $previouslyRecordedPet->id)->count());
    }

    public function test_add_record_page_lists_only_eligible_pets_and_supports_search_filters_and_pagination(): void
    {
        $admin = $this->admin();
        $this->pet('Alpha Dog', 'Available', 'Dog');
        $this->pet('Beta Cat', 'Available', 'Cat');
        $euthanized = $this->pet('Already Euthanized', 'Euthanized', 'Dog');
        $this->logFor($admin, $euthanized);

        $this->actingAs($admin)->get('/admin/health/euthanasia/create?pet_search=Alpha&species=Dog')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Health/Euthanasia/Create')
                ->where('eligiblePets.total', 1)
                ->where('eligiblePets.data.0.name', 'Alpha Dog')
                ->where('filters.species', 'Dog'));

        for ($index = 1; $index <= 10; $index++) {
            $this->pet('Available Pet ' . $index);
        }

        $this->actingAs($admin)->get('/admin/health/euthanasia/create?eligible_page=2')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('eligiblePets.current_page', 2)
                ->where('eligiblePets.per_page', 10)
                ->has('eligiblePets.data', 2));
    }

    public function test_mobile_single_record_uses_the_same_eligibility_protection(): void
    {
        $admin = $this->admin();
        $pet = $this->pet('Mobile Pet');
        $payload = $this->recordData($pet->id);

        $this->withToken($admin->issueApiToken())
            ->postJson('/api/mobile/euthanasia-logs', $payload)
            ->assertCreated();

        $this->withToken($admin->issueApiToken())
            ->postJson('/api/mobile/euthanasia-logs', $payload)
            ->assertUnprocessable();

        $this->assertDatabaseCount('euthanasia_logs', 1);
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'status' => 'Euthanized']);
    }

    public function test_existing_euthanasia_log_can_still_be_viewed_edited_and_deleted(): void
    {
        $admin = $this->admin();
        $pet = $this->pet('Editable Pet');
        $this->actingAs($admin)->post('/admin/health/euthanasia', $this->recordData($pet->id));
        $log = EuthanasiaLog::firstOrFail();

        $this->actingAs($admin)->get('/admin/health/euthanasia/' . $log->id)->assertOk();
        $this->actingAs($admin)->get('/admin/health/euthanasia/' . $log->id . '/edit')->assertOk();
        $this->actingAs($admin)->put('/admin/health/euthanasia/' . $log->id, [
            ...$this->recordData($pet->id),
            'reason' => 'Updated reason',
        ])->assertRedirect('/admin/health/euthanasia/' . $log->id);

        $this->assertDatabaseHas('euthanasia_logs', [
            'id' => $log->id,
            'pet_id' => $pet->id,
            'reason' => 'Updated reason',
        ]);

        $this->actingAs($admin)->delete('/admin/health/euthanasia/' . $log->id)
            ->assertRedirect('/admin/health/euthanasia');
        $this->assertDatabaseMissing('euthanasia_logs', ['id' => $log->id]);
    }

    public function test_pet_with_euthanasia_record_cannot_be_marked_available_via_admin_or_mobile(): void
    {
        $admin = $this->admin();
        $pet = $this->pet('Recorded Pet', 'Euthanized');
        $this->logFor($admin, $pet);
        $update = [
            'name' => $pet->name,
            'species' => $pet->species,
            'breed' => $pet->breed,
            'age' => 3,
            'gender' => $pet->gender,
            'description' => '',
            'status' => 'Available',
            'date_received' => '2026-11-01',
            'size' => $pet->size,
        ];

        $this->actingAs($admin)->put('/admin/pets/' . $pet->id, $update)
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'status' => 'Euthanized']);

        $this->withToken($admin->issueApiToken())->putJson('/api/mobile/pets/' . $pet->id, $update)
            ->assertUnprocessable();
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'status' => 'Euthanized']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'Active']);
    }

    private function pet(string $name, string $status = 'Available', string $species = 'Dog'): Pet
    {
        return Pet::create([
            'name' => $name,
            'species' => $species,
            'size' => 'medium',
            'gender' => 'Unknown',
            'status' => $status,
            'breed' => 'Mixed',
        ]);
    }

    private function recordData(?int $petId = null): array
    {
        return [
            ...($petId ? ['pet_id' => $petId] : []),
            'reason' => 'Severe illness',
            'euthanasia_date' => '2026-11-16',
            'veterinarian' => 'Dr. Shelter',
            'notes' => 'Shared case notes',
        ];
    }

    private function logFor(User $admin, Pet $pet): void
    {
        EuthanasiaLog::create([
            'pet_id' => $pet->id,
            'user_id' => $admin->id,
            'reason' => 'Existing record',
            'euthanasia_date' => '2026-11-15 10:00:00',
            'veterinarian' => 'Dr. Shelter',
        ]);
    }
}
