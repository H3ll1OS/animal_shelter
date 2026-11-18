<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('label');
                $table->text('description');
                $table->boolean('is_system')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('module', 100);
                $table->string('action', 100);
                $table->string('label');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE permissions MODIFY module VARCHAR(100) NOT NULL, MODIFY action VARCHAR(100) NOT NULL');
        }
        $hasPermissionUnique = collect(Schema::getIndexes('permissions'))
            ->contains(fn (array $index) => $index['name'] === 'permissions_module_action_unique');
        if (! $hasPermissionUnique) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->unique(['module', 'action']);
            });
        }

        if (! Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_name');
                $table->string('actor_email')->nullable();
                $table->string('action', 100);
                $table->string('module', 100);
                $table->unsignedBigInteger('subject_user_id')->nullable()->index();
                $table->string('subject_name')->nullable();
                $table->string('subject_email')->nullable();
                $table->json('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['module', 'action']);
                $table->index(['created_at', 'actor_id']);
            });
        }

        $now = now();
        foreach ([
            ['key' => 'superadmin', 'label' => 'Super Admin', 'description' => 'Full access to system administration, account controls, and all application modules.', 'is_system' => true],
            ['key' => 'admin', 'label' => 'Administrator', 'description' => 'Manages daily shelter operations without access to Super Admin controls.', 'is_system' => true],
            ['key' => 'user', 'label' => 'Registered User', 'description' => 'Uses public shelter features and manages their own account.', 'is_system' => true],
        ] as $role) {
            DB::table('roles')->updateOrInsert(['key' => $role['key']], $role + ['created_at' => $now, 'updated_at' => $now]);
        }

        $definitions = [
            'dashboard' => ['view'],
            'pets' => ['view', 'create', 'edit', 'delete'],
            'adoptions' => ['view', 'edit', 'approve'],
            'post_adoption' => ['view', 'create', 'edit', 'delete'],
            'health' => ['view', 'create', 'edit', 'delete'],
            'vaccinations' => ['view', 'create', 'edit', 'delete'],
            'euthanasia' => ['view', 'create', 'edit', 'delete'],
            'donations' => ['view', 'create', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'superadmin' => ['view', 'manage'],
        ];

        $permissionRows = [];
        foreach ($definitions as $module => $actions) {
            foreach ($actions as $action) {
                $permissionRows[] = [
                    'module' => $module,
                    'action' => $action,
                    'label' => ucfirst(str_replace('_', ' ', $module)) . ' ' . ucfirst($action),
                    'description' => ucfirst($action) . ' access for the ' . str_replace('_', ' ', $module) . ' module.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach ($permissionRows as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['module' => $permission['module'], 'action' => $permission['action']],
                $permission,
            );
        }

        $superAdminRoleId = DB::table('roles')->where('key', 'superadmin')->value('id');
        $adminRoleId = DB::table('roles')->where('key', 'admin')->value('id');
        $permissions = DB::table('permissions')->get(['id', 'module', 'action']);
        $grants = [];
        foreach ($permissions as $permission) {
            $grants[] = ['role_id' => $superAdminRoleId, 'permission_id' => $permission->id];
            if (! in_array($permission->module, ['users', 'superadmin'], true)
                || ($permission->module === 'users' && in_array($permission->action, ['view', 'edit'], true))) {
                $grants[] = ['role_id' => $adminRoleId, 'permission_id' => $permission->id];
            }
        }
        DB::table('role_permissions')->insertOrIgnore($grants);
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_login_at');
        });
    }
};
