<?php

namespace Database\Seeders;

use App\Models\HealthRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Rename the class for better clarity
class HealthRecordSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        HealthRecord::create([
            'pet_id' => 1, 
            'diagnosis' => 'Routine Feeding',
            'description' => 'Fed Bella her morning meal - 2 cups of dry kibble.',
            'reported_date' => '2023-12-01',
            'veterinarian' => 'Volunteer Staff', 
            'is_critical' => false,
            'notes' => 'Bella ate everything enthusiastically.',
        ]);

        
        HealthRecord::create([
            'pet_id' => 2, 
            'diagnosis' => 'Upper Respiratory Infection (URI) Suspected',
            'description' => 'Noticed sneezing, mild eye discharge, and decreased appetite. Temperature elevated.',
            'reported_date' => '2023-12-08', 
            'veterinarian' => 'Dr. Jones',
            'is_critical' => true, 
            'notes' => 'Moved animal to isolation wing for monitoring.',
        ]);

        
        HealthRecord::create([
            'pet_id' => 3, 
            'diagnosis' => 'Post-Surgical Checkup',
            'description' => 'Suture site clean and healing well. Activity levels are normal.',
            'reported_date' => '2023-12-05',
            'veterinarian' => 'Dr. Smith',
            'is_critical' => false,
            'notes' => 'Approved for light yard exercise.',
        ]);
    }
}