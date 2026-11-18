<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        Schema::table('pets', function (Blueprint $table) {
            $table->enum('status', ['Available', 'Adopted', 'Pending', 'Sick', 'Fostered', 'Euthanized'])
                ->default('Available')
                ->change();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        if (DB::table('pets')->where('status', 'Euthanized')->exists()) {
            throw new RuntimeException('Cannot remove the Euthanized status while pets still use it.');
        }

        Schema::table('pets', function (Blueprint $table) {
            $table->enum('status', ['Available', 'Adopted', 'Pending', 'Sick', 'Fostered'])
                ->default('Available')
                ->change();
        });
    }
};
