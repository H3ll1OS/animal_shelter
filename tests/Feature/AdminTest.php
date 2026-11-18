<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'Active',
        ]);

        // Act as the admin user
        $response = $this->actingAs($admin)->get('/admin');

        // Assert that the response is successful (not 403)
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        // The users.role enum defines regular accounts as "user".
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'Active',
        ]);

        // Act as the volunteer user
        $response = $this->actingAs($user)->get('/admin');

        // Assert that the response is 403 unauthorized
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard()
    {
        // Try to access admin without authentication
        $response = $this->get('/admin');

        // Should redirect to login
        $response->assertRedirect('/login');
    }

    public function test_super_admin_is_denied_access_to_regular_admin_panel()
    {
        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'Active',
        ]);

        $this->actingAs($superAdmin)->get('/admin')->assertStatus(403);
    }

    public function test_regular_admin_cannot_access_super_admin_user_management()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'Active',
        ]);

        $this->actingAs($admin)->get('/superadmin/users')->assertStatus(403);
    }

    public function test_admin_user_management_routes_are_removed(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)->get('/admin/users')->assertNotFound();
        $this->actingAs($admin)->patch('/admin/users/' . $admin->id . '/status', ['status' => 'Blocked'])->assertNotFound();
    }

    public function test_super_admin_can_access_dedicated_dashboard()
    {
        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'Active',
        ]);

        $this->actingAs($superAdmin)->get('/superadmin')->assertStatus(200);
    }

    public function test_legacy_user_management_url_redirects_to_super_admin_url()
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);

        $this->actingAs($superAdmin)->get('/developer/users')
            ->assertRedirect('/superadmin/accounts');
    }
}
