<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('donations', 'rejected_by')) {
            Schema::table('donations', function (Blueprint $table) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('verified_by');
                $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('donations', 'status')) {
            DB::table('donations')
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhereNotIn('status', ['Pending', 'Verified', 'Rejected']);
                })
                ->update(['status' => 'Pending']);
        }

        if (Schema::hasColumn('donations', 'status')
            && Schema::hasColumn('donations', 'verified_by')
            && Schema::hasColumn('donations', 'rejected_by')) {
            DB::table('donations')
                ->where('status', 'Rejected')
                ->whereNull('rejected_by')
                ->whereNotNull('verified_by')
                ->update(['rejected_by' => DB::raw('verified_by')]);

            DB::table('donations')
                ->where('status', 'Rejected')
                ->whereNotNull('rejected_by')
                ->update(['verified_by' => null]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('donations', 'rejected_by')) {
            Schema::table('donations', function (Blueprint $table) {
                $table->dropForeign(['rejected_by']);
                $table->dropColumn('rejected_by');
            });
        }
    }
};
