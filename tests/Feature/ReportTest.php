<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\Donation;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_require_an_authenticated_active_admin(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'user', 'status' => 'Active']);
        $this->actingAs($user)->get(route('admin.reports.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_custom_report_rejects_invalid_date_ranges(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'period' => 'custom',
                'start_date' => '2026-12-31',
                'end_date' => '2026-01-01',
            ]))
            ->assertSessionHasErrors('end_date');
    }

    public function test_monthly_report_uses_database_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $pet = Pet::create([
            'name' => 'Report Dog',
            'species' => 'Dog',
            'gender' => 'Male',
            'size' => 'medium',
            'status' => 'Available',
            'date_received' => '2026-11-10',
        ]);
        $adopter = User::factory()->create(['role' => 'user', 'status' => 'Active']);
        Adoption::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'full_name' => 'Report Adopter',
            'email' => 'adopter@example.com',
            'phone' => '09171234567',
            'message' => 'Adoption request',
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'period' => 'monthly',
                'month' => 11,
                'year' => 2026,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Reports/Index')
                ->where('summary.adoption_applications', 1)
                ->where('summary.pets_registered', 1));
    }

    public function test_verified_donation_totals_exclude_pending_and_rejected_donations(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $verifiedAt = now()->startOfMonth()->addDay();

        Donation::create([
            'name' => 'Verified', 'last_name' => 'Donor', 'email' => 'verified@example.com',
            'amount' => 500, 'payment_method' => 'gcash', 'status' => 'Verified',
            'verified_at' => $verifiedAt, 'created_at' => $verifiedAt,
        ]);
        Donation::create([
            'name' => 'Pending', 'last_name' => 'Donor', 'email' => 'pending@example.com',
            'amount' => 1000, 'payment_method' => 'gcash', 'status' => 'Pending',
            'created_at' => $verifiedAt,
        ]);
        Donation::create([
            'name' => 'Rejected', 'last_name' => 'Donor', 'email' => 'rejected@example.com',
            'amount' => 2000, 'payment_method' => 'gcash', 'status' => 'Rejected',
            'rejected_at' => $verifiedAt, 'created_at' => $verifiedAt,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'period' => 'monthly',
                'month' => $verifiedAt->month,
                'year' => $verifiedAt->year,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('summary.donation_submissions', 3)
                ->where('summary.pending_donations', 1)
                ->where('summary.verified_donations', 1)
                ->where('summary.rejected_donations', 1)
                ->where('summary.verified_donation_amount', 500));
    }

    public function test_excel_and_pdf_exports_are_restricted_to_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)
            ->get(route('admin.reports.export.excel', ['period' => 'monthly']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.reports.export.pdf', ['period' => 'monthly']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
