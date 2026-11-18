<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\Pet;
use App\Models\PostAdoptionVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAdoptionMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function adoptedPair(string $adoptionStatus = 'Approved', string $petStatus = 'Adopted'): array
    {
        $adopter = User::factory()->create(['role' => 'user', 'status' => 'Active']);
        $pet = Pet::create(['name' => 'Milo', 'species' => 'Dog', 'size' => 'small', 'gender' => 'Male', 'status' => $petStatus]);
        $adoption = Adoption::create([
            'pet_id' => $pet->id, 'user_id' => $adopter->id, 'full_name' => $adopter->name,
            'email' => $adopter->email, 'phone' => '09170000000', 'message' => 'Application', 'status' => $adoptionStatus,
        ]);

        return [$adopter, $pet, $adoption];
    }

    public function test_admin_can_schedule_a_follow_up_for_an_approved_adopted_pet(): void
    {
        [, , $adoption] = $this->adoptedPair();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        PostAdoptionVisit::create([
            'adoption_id' => $adoption->id,
            'pet_id' => $adoption->pet_id,
            'adopter_id' => $adoption->user_id,
            'created_by' => $admin->id,
            'scheduled_date' => now()->addDays(10)->toDateString(),
            'monitoring_method' => 'Home Visit',
            'schedule_status' => 'Pending',
        ]);

        $this->actingAs($admin)->post(route('admin.post-adoption.store'), [
            'adoption_id' => $adoption->id, 'scheduled_date' => now()->addDays(3)->toDateString(),
            'monitoring_method' => 'Phone Call', 'purpose' => 'Welfare check',
        ])->assertRedirect(route('admin.post-adoption.schedules'));

        $this->assertDatabaseHas('post_adoption_visits', [
            'adoption_id' => $adoption->id, 'pet_id' => $adoption->pet_id,
            'adopter_id' => $adoption->user_id, 'schedule_status' => 'Pending',
        ]);

        $newSchedule = PostAdoptionVisit::where('scheduled_date', now()->addDays(3)->toDateString())->firstOrFail();
        $this->actingAs($admin)
            ->get(route('admin.post-adoption.schedules'), [
                'X-Inertia' => 'true',
                'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
            ])
            ->assertOk()
            ->assertJsonPath('props.visits.data.0.id', $newSchedule->id);
    }

    public function test_unapproved_adoption_cannot_be_scheduled(): void
    {
        [, , $adoption] = $this->adoptedPair('Pending', 'Available');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)->post(route('admin.post-adoption.store'), [
            'adoption_id' => $adoption->id, 'scheduled_date' => now()->addDays(3)->toDateString(),
            'monitoring_method' => 'Home Visit',
        ])->assertNotFound();

        $this->assertDatabaseCount('post_adoption_visits', 0);
    }

    public function test_regular_user_cannot_access_monitoring_pages(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'Active']);

        $this->actingAs($user)->get(route('admin.post-adoption.dashboard'))->assertForbidden();
    }

    public function test_monitoring_completion_creates_at_most_one_follow_up(): void
    {
        [, $pet, $adoption] = $this->adoptedPair();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $visit = PostAdoptionVisit::create([
            'adoption_id' => $adoption->id,
            'pet_id' => $pet->id,
            'adopter_id' => $adoption->user_id,
            'created_by' => $admin->id,
            'scheduled_date' => now()->subDay()->toDateString(),
            'monitoring_method' => 'Home Visit',
            'schedule_status' => 'Pending',
        ]);
        $completion = [
            'actual_visit_date' => now()->toDateString(),
            'monitoring_method' => 'Home Visit',
            'pet_status' => 'Alive',
            'health_condition' => 'Healthy',
            'living_condition' => 'Good',
            'ownership_status' => 'With Original Adopter',
            'findings' => 'Pet is in good condition.',
            'next_follow_up_date' => now()->addDays(10)->toDateString(),
        ];

        $this->actingAs($admin)
            ->post(route('admin.post-adoption.complete', $visit), $completion)
            ->assertRedirect(route('admin.post-adoption.show', $visit));
        $this->actingAs($admin)
            ->post(route('admin.post-adoption.complete', $visit), $completion)
            ->assertStatus(422);

        $this->assertDatabaseCount('post_adoption_visits', 2);
        $this->assertDatabaseHas('post_adoption_visits', [
            'id' => $visit->id,
            'schedule_status' => 'Completed',
        ]);
        $this->assertDatabaseHas('post_adoption_visits', [
            'purpose' => 'Follow-up after monitoring visit #'.$visit->id,
            'schedule_status' => 'Pending',
        ]);
    }
}
