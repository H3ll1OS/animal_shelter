<?php

namespace Database\Seeders;

use App\Models\HealthRecord;
use App\Models\Pet;
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
        
        $records = [
            ['pet' => 'Buddy', 'diagnosis' => 'Routine Feeding',
            'description' => 'Fed Buddy his morning meal - 2 cups of dry kibble.',
            'reported_date' => '2023-12-01',
            'veterinarian' => 'Volunteer Staff', 
            'is_critical' => false,
            'notes' => 'Buddy ate everything enthusiastically.'],

        
            ['pet' => 'Lucy', 'diagnosis' => 'Upper Respiratory Infection (URI) Suspected',
            'description' => 'Noticed sneezing, mild eye discharge, and decreased appetite. Temperature elevated.',
            'reported_date' => '2023-12-08', 
            'veterinarian' => 'Dr. Jones',
            'is_critical' => true, 
            'notes' => 'Moved animal to isolation wing for monitoring.'],

        
            ['pet' => 'Whiskers', 'diagnosis' => 'Post-Surgical Checkup',
            'description' => 'Suture site clean and healing well. Activity levels are normal.',
            'reported_date' => '2023-12-05',
            'veterinarian' => 'Dr. Smith',
            'is_critical' => false,
            'notes' => 'Approved for light yard exercise.'],
        ];

        foreach ($records as $record) {
            $pet = Pet::where('name', $record['pet'])->first();
            if (! $pet) continue;

            unset($record['pet']);
            HealthRecord::firstOrCreate([
                'pet_id' => $pet->id,
                'diagnosis' => $record['diagnosis'],
            ], $record);
        }
    }
}
