<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite stores Laravel enum columns as strings and needs no ALTER.
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE pets MODIFY status ENUM('Available','Adopted','Pending','Sick','Fostered','Euthanized') DEFAULT 'Available'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            if (DB::table('pets')->where('status', 'Euthanized')->exists()) {
                throw new RuntimeException('Cannot remove the Euthanized status while pets still use it.');
            }

            DB::statement("ALTER TABLE pets MODIFY status ENUM('Available','Adopted','Pending','Sick','Fostered') DEFAULT 'Available'");
        }
    }
};
