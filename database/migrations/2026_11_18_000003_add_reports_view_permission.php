<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('permissions')->where('module', 'reports')->where('action', 'view')->exists()) {
            $now = now();
            DB::table('permissions')->insert([
                'label' => 'Reports View',
                'description' => 'View reports and analytics.',
                'module' => 'reports',
                'action' => 'view',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionId = DB::table('permissions')
            ->where('module', 'reports')
            ->where('action', 'view')
            ->value('id');

        foreach (['admin', 'superadmin'] as $roleKey) {
            $roleId = DB::table('roles')->where('key', $roleKey)->value('id');
            if ($roleId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep access assignments intact if application code is rolled back.
    }
};
