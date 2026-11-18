<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = [
        'users', 'pets', 'adoptions', 'donations', 'euthanasia_logs', 'health_records', 'vaccine_schedules',
    ];

    private array $relationships = [
        'adoptions' => [
            ['pet_id', 'pets', 'cascade'],
            ['user_id', 'users', 'cascade'],
        ],
        'donations' => [
            ['user_id', 'users', 'restrict'],
        ],
        'euthanasia_logs' => [
            ['pet_id', 'pets', 'cascade'],
            ['user_id', 'users', 'cascade'],
        ],
        'health_records' => [
            ['pet_id', 'pets', 'cascade'],
        ],
        'vaccine_schedules' => [
            ['pet_id', 'pets', 'cascade'],
        ],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Existing MyISAM tables silently discarded their declared foreign keys.
        // Convert them without changing their columns or rows, then install the
        // constraints that the original migrations intended to create.
        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
        }

        foreach ($this->relationships as $table => $relationships) {
            foreach ($relationships as [$column, $parent, $onDelete]) {
                $name = "{$table}_{$column}_foreign";
                $foreignKeys = collect(Schema::getForeignKeys($table));

                if ($foreignKeys->contains(fn (array $foreignKey) => $foreignKey['name'] === $name)) {
                    continue;
                }

                $indexes = collect(Schema::getIndexes($table));
                if ($indexes->contains(fn (array $index) => $index['name'] === $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
                }

                Schema::table($table, function (Blueprint $blueprint) use ($column, $parent, $onDelete) {
                    $foreign = $blueprint->foreign($column)->references('id')->on($parent);
                    $onDelete === 'cascade' ? $foreign->cascadeOnDelete() : $foreign->restrictOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->relationships as $table => $relationships) {
            foreach ($relationships as [$column]) {
                $name = "{$table}_{$column}_foreign";
                if (collect(Schema::getForeignKeys($table))->contains(fn (array $foreignKey) => $foreignKey['name'] === $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign($name));
                }
            }
        }

        foreach ($this->tables as $table) {
            DB::statement("ALTER TABLE `{$table}` ENGINE=MyISAM");
        }
    }
};
