<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('donations', 'user_id')) {
            return;
        }

        if (\Illuminate\Support\Facades\DB::table('donations')->whereNotNull('user_id')->exists()) {
            throw new RuntimeException('Cannot remove donation user links while donations still reference users.');
        }

        foreach (Schema::getForeignKeys('donations') as $foreignKey) {
            if ($foreignKey['columns'] === ['user_id']) {
                Schema::table('donations', fn (Blueprint $table) => $table->dropForeign($foreignKey['name']));
            }
        }

        foreach (Schema::getIndexes('donations') as $index) {
            if ($index['columns'] === ['user_id'] && ! $index['primary'] && ! $index['unique']) {
                Schema::table('donations', fn (Blueprint $table) => $table->dropIndex($index['name']));
            }
        }

        Schema::table('donations', fn (Blueprint $table) => $table->dropColumn('user_id'));
    }
};
