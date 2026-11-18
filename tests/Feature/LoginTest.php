<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_login_destinations_render_the_existing_login_page(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn ($page) => $page->component('Auth/Login'));
        $this->get('/superadmin/login')->assertOk()->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_super_admin_login_redirects_to_dedicated_panel()
    {
        $superAdmin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        // Attempt login
        $response = $this->post('/login', [
            'email' => $superAdmin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/superadmin');
    }

    public function test_admin_login_redirects_to_admin_dashboard()
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        // Attempt login
        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
    }

    public function test_regular_user_login_redirects_to_home()
    {
        // Create a regular user (the valid non-admin users.role value).
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        // Attempt login
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Should redirect to /
        $response->assertRedirect('/');
    }
}
