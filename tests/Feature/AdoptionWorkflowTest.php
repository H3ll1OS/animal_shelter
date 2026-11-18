<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdoptionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_adoption_decision_routes_only_accept_post_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        [$adoption] = $this->pendingApplication();

        $this->actingAs($admin)
            ->get('/admin/adoptions/'.$adoption->id.'/approve')
            ->assertStatus(405);
        $this->assertSame('Pending', $adoption->fresh()->status);
    }

    public function test_only_pending_adoptions_for_available_pets_can_be_approved(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        [$adoption, $pet] = $this->pendingApplication();

        $this->actingAs($admin)
            ->post('/admin/adoptions/'.$adoption->id.'/approve')
            ->assertRedirect(route('admin.adoptions.index'))
            ->assertSessionHas('success');

        $this->assertSame('Approved', $adoption->fresh()->status);
        $this->assertSame('Adopted', $pet->fresh()->status);

        $this->actingAs($admin)
            ->post('/admin/adoptions/'.$adoption->id.'/reject')
            ->assertSessionHas('error', 'Only pending applications can be rejected.');
        $this->assertSame('Approved', $adoption->fresh()->status);
    }

    public function test_a_second_pending_application_cannot_be_approved_after_pet_is_adopted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        [$first, $pet] = $this->pendingApplication();
        $second = Adoption::create([
            'pet_id' => $pet->id,
            'user_id' => User::factory()->create()->id,
            'full_name' => 'Second Applicant',
            'email' => 'second@example.test',
            'phone' => '09170000002',
            'message' => '',
            'document_path' => '[]',
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)->post('/admin/adoptions/'.$first->id.'/approve');
        $this->actingAs($admin)
            ->post('/admin/adoptions/'.$second->id.'/approve')
            ->assertSessionHas('error', 'This pet is no longer available for adoption.');

        $this->assertSame('Approved', $first->fresh()->status);
        $this->assertSame('Pending', $second->fresh()->status);
        $this->assertSame('Adopted', $pet->fresh()->status);
    }

    public function test_client_cannot_create_duplicate_pending_application_for_same_pet(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user', 'status' => 'Active']);
        $pet = $this->availablePet();
        $submission = [
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => '09170000001',
            'message' => '',
            'documents' => [UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf')],
        ];

        $this->actingAs($user)->post('/adopt/'.$pet->id, $submission)->assertSessionHas('success');
        $this->actingAs($user)->post('/adopt/'.$pet->id, $submission)
            ->assertSessionHas('error', 'You already have a pending application for this pet.');

        $this->assertDatabaseCount('adoptions', 1);
    }

    private function pendingApplication(): array
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'Active']);
        $pet = $this->availablePet();
        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => '09170000001',
            'message' => '',
            'document_path' => '[]',
            'status' => 'Pending',
        ]);

        return [$adoption, $pet];
    }

    private function availablePet(): Pet
    {
        return Pet::create([
            'name' => 'Milo',
            'species' => 'Dog',
            'size' => 'small',
            'gender' => 'Male',
            'status' => 'Available',
        ]);
    }
}
