<?php

// database/migrations/...add_soft_deletes_to_vaccine_schedules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vaccine_schedules', function (Blueprint $table) {
            $table->softDeletes(); // Adds the 'deleted_at' column
        });
    }

    public function down(): void
    {
        Schema::table('vaccine_schedules', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Removes the 'deleted_at' column
        });
    }
};
