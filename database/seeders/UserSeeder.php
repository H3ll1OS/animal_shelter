<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (!User::where('email', 'dev@shelter.com')->exists()) {
            User::create([
                'name' => 'Developer Account',
                'email' => 'dev@shelter.com',
                'password' => Hash::make('password'), 
                'role' => 'developer', 
                'status' => 'Active',
            ]);
        }

        if (!User::where('email', 'admin@shelter.com')->exists()) {
            User::create([
                'name' => 'Admin Shelter',
                'email' => 'admin@shelter.com',
                'password' => Hash::make('password'), 
                'role' => 'admin', 
                'status' => 'Active',
            ]);
        }

        if (!User::where('email', 'testuser@shelter.com')->exists()) {
            User::create([
                'name' => 'General Volunteer',
                'email' => 'testuser@shelter.com',
                'password' => Hash::make('password'), 
                'role' => 'user', 
                'status' => 'Active',
            ]);
        }
    }
}