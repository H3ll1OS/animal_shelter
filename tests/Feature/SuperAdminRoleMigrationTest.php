<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperAdminRoleMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_developer_role_migrates_to_superadmin_and_can_be_reversed(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'developer', 'superadmin', 'user'])->default('user')->change();
        });

        $user = User::factory()->create(['role' => 'developer']);
        $migration = require database_path('migrations/2026_11_16_000000_replace_developer_role_with_superadmin.php');

        $migration->up();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'superadmin',
        ]);

        $migration->down();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'developer']);
    }
}
