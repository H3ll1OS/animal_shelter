<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        $this->call(UserSeeder::class); 

       
        $this->call(AnimalSeeder::class);
        $this->call(VaccinationSeeder::class);
        $this->call(HealthRecordSeeder::class);
        $this->call(EuthanasiaLogSeeder::class);
        
        
    }
}