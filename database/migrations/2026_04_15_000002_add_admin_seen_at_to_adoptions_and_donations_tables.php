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
        Schema::table('adoptions', function (Blueprint $table) {
            $table->timestamp('admin_seen_at')->nullable()->after('decision_seen_at');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->timestamp('admin_seen_at')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adoptions', function (Blueprint $table) {
            $table->dropColumn('admin_seen_at');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('admin_seen_at');
        });
    }
};
