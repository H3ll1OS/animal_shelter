<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_developer_login_redirects_to_developer_dashboard()
    {
        // Create a developer user
        $developer = User::factory()->create([
            'role' => 'developer',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        // Attempt login
        $response = $this->post('/login', [
            'email' => $developer->email,
            'password' => 'password',
        ]);

        // Should redirect to /developer/users
        $response->assertRedirect('/developer/users');
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

        // Should redirect to /admin
        $response->assertRedirect('/admin');
    }

    public function test_volunteer_login_redirects_to_home()
    {
        // Create a volunteer user
        $volunteer = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        // Attempt login
        $response = $this->post('/login', [
            'email' => $volunteer->email,
            'password' => 'password',
        ]);

        // Should redirect to /
        $response->assertRedirect('/');
    }
}
