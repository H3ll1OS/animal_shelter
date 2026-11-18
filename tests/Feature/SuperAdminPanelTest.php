<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_access_super_admin_pages_or_mutation_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);

        $this->actingAs($admin)->get('/superadmin')->assertStatus(403);
        $this->actingAs($admin)->get('/superadmin/accounts')->assertStatus(403);
    }

    public function test_super_admin_can_filter_accounts_and_view_database_driven_overview(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);
        User::factory()->create(['name' => 'Shelter Admin', 'role' => 'admin', 'status' => 'Active']);
        User::factory()->create(['name' => 'Blocked Volunteer', 'role' => 'user', 'status' => 'Blocked']);

        $this->actingAs($superAdmin)
            ->get('/superadmin/accounts?search=Blocked&status=Blocked')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('SuperAdmin/Accounts/Index')
                ->where('users.total', 1)
                ->where('users.data.0.name', 'Blocked Volunteer'));

        $this->actingAs($superAdmin)->get('/superadmin')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('SuperAdmin/Dashboard')
                ->where('stats.accounts', 3)
                ->where('stats.administrators', 1)
                ->where('stats.inactive', 1)
                ->missing('recentActivities'));
    }

    public function test_super_admin_account_changes_are_audited_without_passwords(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);

        $response = $this->actingAs($superAdmin)->post('/superadmin/accounts', [
            'name' => 'New Administrator',
            'email' => 'new-admin@example.test',
            'role' => 'admin',
            'status' => 'Active',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ]);

        $account = User::where('email', 'new-admin@example.test')->firstOrFail();
        $response->assertRedirect('/superadmin/accounts/'.$account->id);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'account_created',
            'subject_user_id' => $account->id,
        ]);
        $this->assertStringNotContainsString('strong-password', json_encode(ActivityLog::latest()->first()->details));
    }

    public function test_last_active_super_admin_cannot_be_demoted_deactivated_or_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);

        $this->actingAs($superAdmin)->put('/superadmin/accounts/'.$superAdmin->id, [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'role' => 'admin',
            'status' => 'Blocked',
        ])->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id, 'role' => 'superadmin', 'status' => 'Active']);
        $this->actingAs($superAdmin)->delete('/superadmin/accounts/'.$superAdmin->id)->assertStatus(422);
    }

    public function test_operational_permissions_are_enforced_for_regular_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $role = Role::where('key', 'admin')->firstOrFail();
        $permission = Permission::where('module', 'dashboard')->where('action', 'view')->firstOrFail();
        $role->permissions()->detach($permission->id);

        $this->actingAs($admin)->get('/admin')->assertStatus(403);

        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);
        $this->actingAs($superAdmin)->get('/superadmin/accounts')->assertOk();
    }

    public function test_reports_route_uses_its_own_permission(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $role = Role::where('key', 'admin')->firstOrFail();
        $reportsPermission = Permission::where('module', 'reports')->where('action', 'view')->firstOrFail();
        $dashboardPermission = Permission::where('module', 'dashboard')->where('action', 'view')->firstOrFail();

        $role->permissions()->detach($reportsPermission->id);
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertForbidden();

        $role->permissions()->attach($reportsPermission->id);
        $role->permissions()->detach($dashboardPermission->id);
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
    }

    public function test_removed_super_admin_modules_are_not_routable(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);

        $this->actingAs($superAdmin)->get('/superadmin/accounts/create')->assertOk();
        foreach ([
            '/superadmin/roles',
            '/superadmin/permissions',
            '/superadmin/security',
            '/superadmin/profile',
        ] as $path) {
            $this->actingAs($superAdmin)->get($path)->assertNotFound();
        }
        $this->actingAs($superAdmin)->put('/superadmin/roles/1/permissions', ['permission_ids' => []])->assertNotFound();
        $this->actingAs($superAdmin)->patch('/superadmin/profile', ['name' => 'Changed', 'email' => 'changed@example.test'])->assertNotFound();
    }

    public function test_super_admin_can_manage_role_access_and_review_activity_logs(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);
        $adminRole = Role::where('key', 'admin')->firstOrFail();
        $dashboardPermission = Permission::where('module', 'dashboard')->where('action', 'view')->firstOrFail();
        ActivityLog::create([
            'actor_id' => $superAdmin->id,
            'actor_name' => $superAdmin->name,
            'actor_email' => $superAdmin->email,
            'action' => 'account_created',
            'module' => 'users',
            'subject_name' => 'New Administrator',
            'subject_email' => 'new-admin@example.test',
            'details' => ['role' => 'admin'],
        ]);

        $this->actingAs($superAdmin)->get('/superadmin/access-management')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('SuperAdmin/AccessManagement/Index')
                ->has('roles')
                ->has('permissions.dashboard'));

        $this->actingAs($superAdmin)->put('/superadmin/roles/'.$adminRole->id.'/access', [
            'description' => 'Shelter operations with dashboard access.',
            'permission_ids' => [$dashboardPermission->id],
        ])->assertRedirect();
        $this->assertSame(
            [$dashboardPermission->id],
            $adminRole->fresh()->permissions()->pluck('permissions.id')->all(),
        );
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'role_access_updated',
            'module' => 'permissions',
        ]);

        $this->actingAs($superAdmin)->get('/superadmin/activity-logs?search=New%20Administrator')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('SuperAdmin/ActivityLogs/Index')
                ->where('logs.total', 1)
                ->where('logs.data.0.subject_name', 'New Administrator'));
    }

    public function test_admin_cannot_assign_super_admin_through_mobile_user_management_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $target = User::factory()->create(['role' => 'user', 'status' => 'Active']);
        $token = $admin->issueApiToken();

        $this->withToken($token)->putJson('/api/mobile/users/'.$target->id.'/role-status', [
            'role' => 'superadmin',
            'status' => 'Active',
        ])->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'user']);
    }

    public function test_admin_api_obeys_database_backed_operational_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $adminRole = Role::where('key', 'admin')->firstOrFail();
        $createPets = Permission::where('module', 'pets')->where('action', 'create')->firstOrFail();
        $adminRole->permissions()->detach($createPets->id);

        $this->withToken($admin->issueApiToken())->postJson('/api/mobile/pets', [])
            ->assertForbidden();
    }

    public function test_super_admin_logout_uses_existing_authentication_flow(): void
    {
        $superAdmin = User::factory()->create(['role' => 'superadmin', 'status' => 'Active']);

        $this->actingAs($superAdmin)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
