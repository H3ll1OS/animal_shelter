<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (! Schema::hasColumn('donations', 'status')) {
                $table->string('status', 20)->default('Pending')->after('payment_method');
            }
            if (! Schema::hasColumn('donations', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (! Schema::hasColumn('donations', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('rejection_reason');
            }
            if (! Schema::hasColumn('donations', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
            if (! Schema::hasColumn('donations', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('verified_at');
            }
        });

        if (! collect(Schema::getForeignKeys('donations'))->contains(
            fn (array $foreignKey) => $foreignKey['columns'] === ['verified_by']
        )) {
            Schema::table('donations', function (Blueprint $table) {
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (collect(Schema::getForeignKeys('donations'))->contains(
            fn (array $foreignKey) => $foreignKey['columns'] === ['verified_by']
        )) {
            Schema::table('donations', fn (Blueprint $table) => $table->dropForeign(['verified_by']));
        }

        Schema::table('donations', function (Blueprint $table) {
            $columns = array_filter([
                'status' => Schema::hasColumn('donations', 'status'),
                'rejection_reason' => Schema::hasColumn('donations', 'rejection_reason'),
                'verified_by' => Schema::hasColumn('donations', 'verified_by'),
                'verified_at' => Schema::hasColumn('donations', 'verified_at'),
                'rejected_at' => Schema::hasColumn('donations', 'rejected_at'),
            ]);
            if ($columns) {
                $table->dropColumn(array_keys($columns));
            }
        });
    }
};
