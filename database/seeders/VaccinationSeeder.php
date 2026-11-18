<?php

namespace Database\Seeders;

use App\Models\VaccineSchedule;
use App\Models\Pet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaccinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vaccinations = [
            ['pet' => 'Buddy', 'vaccine_type' => 'Rabies Vaccine',
            'last_given_date' => '2023-01-15',
            'next_due_date' => '2024-01-15',
            'notes' => 'First rabies vaccination for Buddy.'],
            ['pet' => 'Whiskers', 'vaccine_type' => 'FVRCP Vaccine',
            'last_given_date' => '2023-02-10',
            'next_due_date' => '2024-02-10',
            'notes' => 'Feline viral rhinotracheitis, calicivirus, and panleukopenia vaccine.'],
            ['pet' => 'Lucy', 'vaccine_type' => 'DHPP Vaccine',
            'last_given_date' => '2023-03-05',
            'next_due_date' => '2024-03-05',
            'notes' => 'Distemper, hepatitis, parvovirus, and parainfluenza vaccine.'],
        ];

        foreach ($vaccinations as $vaccination) {
            $pet = Pet::where('name', $vaccination['pet'])->first();
            if (! $pet) continue;

            unset($vaccination['pet']);
            VaccineSchedule::firstOrCreate([
                'pet_id' => $pet->id,
                'vaccine_type' => $vaccination['vaccine_type'],
            ], $vaccination);
        }
    }
}
