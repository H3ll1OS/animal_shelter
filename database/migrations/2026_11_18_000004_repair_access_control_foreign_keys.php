<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $orphans = [
            'activity_logs.actor_id' => DB::table('activity_logs as logs')
                ->leftJoin('users', 'users.id', '=', 'logs.actor_id')
                ->whereNotNull('logs.actor_id')
                ->whereNull('users.id')
                ->count(),
            'role_permissions.role_id' => DB::table('role_permissions as role_permissions')
                ->leftJoin('roles', 'roles.id', '=', 'role_permissions.role_id')
                ->whereNull('roles.id')
                ->count(),
            'role_permissions.permission_id' => DB::table('role_permissions as role_permissions')
                ->leftJoin('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->whereNull('permissions.id')
                ->count(),
        ];

        $invalidRelations = array_filter($orphans);
        if ($invalidRelations !== []) {
            throw new \RuntimeException(
                'Cannot add access-control foreign keys while orphaned records exist: '.implode(', ', array_keys($invalidRelations))
            );
        }

        foreach (['roles', 'permissions', 'role_permissions', 'activity_logs'] as $table) {
            DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
        }

        $this->addForeignKey('activity_logs', 'actor_id', 'users', 'nullOnDelete');
        $this->addForeignKey('role_permissions', 'role_id', 'roles', 'cascadeOnDelete');
        $this->addForeignKey('role_permissions', 'permission_id', 'permissions', 'cascadeOnDelete');
    }

    public function down(): void
    {
        // Keep the integrity constraints and InnoDB engines when rolling back application code.
    }

    private function addForeignKey(string $table, string $column, string $parent, string $deleteAction): void
    {
        $exists = collect(Schema::getForeignKeys($table))->contains(
            fn (array $foreignKey) => $foreignKey['columns'] === [$column]
                && $foreignKey['foreign_table'] === $parent
        );

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $parent, $deleteAction) {
            $foreignKey = $blueprint->foreign($column)->references('id')->on($parent);
            $foreignKey->{$deleteAction}();
        });
    }
};
