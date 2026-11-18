<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'developer', 'superadmin', 'user') NOT NULL DEFAULT 'user'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'developer', 'superadmin', 'user'])->default('user')->change();
            });
        }

        DB::table('users')->where('role', 'developer')->update(['role' => 'superadmin']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'superadmin', 'user') NOT NULL DEFAULT 'user'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'superadmin', 'user'])->default('user')->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'developer', 'superadmin', 'user') NOT NULL DEFAULT 'user'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'developer', 'superadmin', 'user'])->default('user')->change();
            });
        }

        DB::table('users')->where('role', 'superadmin')->update(['role' => 'developer']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'developer', 'user') NOT NULL DEFAULT 'user'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'developer', 'user'])->default('user')->change();
            });
        }
    }
};
