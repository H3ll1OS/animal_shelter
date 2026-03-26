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
        // Create a volunteer user
        $volunteer = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'Active',
        ]);

        // Act as the volunteer user
        $response = $this->actingAs($volunteer)->get('/admin');

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

    public function test_developer_can_access_developer_dashboard()
    {
        // Create a developer user
        $developer = User::factory()->create([
            'role' => 'developer',
            'status' => 'Active',
        ]);

        // Act as the developer user
        $response = $this->actingAs($developer)->get('/developer');

        // Assert that the response is successful
        $response->assertStatus(200);
    }

    public function test_developer_cannot_access_admin_dashboard()
    {
        // Create a developer user
        $developer = User::factory()->create([
            'role' => 'developer',
            'status' => 'Active',
        ]);

        // Act as the developer user
        $response = $this->actingAs($developer)->get('/admin');

        // Assert that the response is 403 unauthorized
        $response->assertStatus(403);
    }
}
