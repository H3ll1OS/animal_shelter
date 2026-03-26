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
        // Allow Euthanized as a valid pet status
        DB::statement("ALTER TABLE pets MODIFY status ENUM('Available','Adopted','Pending','Sick','Fostered','Euthanized') DEFAULT 'Available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum set
        DB::statement("ALTER TABLE pets MODIFY status ENUM('Available','Adopted','Pending','Sick','Fostered') DEFAULT 'Available'");
    }
};
