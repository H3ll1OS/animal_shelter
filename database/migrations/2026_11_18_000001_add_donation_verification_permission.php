<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('role_permissions')) {
            return;
        }

        $now = now();
        $permissionId = DB::table('permissions')->updateOrInsert(
            ['module' => 'donations', 'action' => 'edit'],
            [
                'label' => 'Donations Edit',
                'description' => 'Verify or reject submitted donations.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $permissionId = DB::table('permissions')
            ->where('module', 'donations')
            ->where('action', 'edit')
            ->value('id');

        foreach (['admin', 'superadmin'] as $roleKey) {
            $roleId = DB::table('roles')->where('key', $roleKey)->value('id');
            if ($roleId && $permissionId) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }
        DB::table('permissions')
            ->where('module', 'donations')
            ->where('action', 'edit')
            ->delete();
    }
};
