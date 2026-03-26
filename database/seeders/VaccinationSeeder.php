<?php

namespace Database\Seeders;

use App\Models\VaccineSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaccinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VaccineSchedule::create([
            'pet_id' => 1,
            'vaccine_type' => 'Rabies Vaccine',
            'last_given_date' => '2023-01-15',
            'next_due_date' => '2024-01-15',
            'notes' => 'First rabies vaccination for Bella.',
        ]);

        VaccineSchedule::create([
            'pet_id' => 2,
            'vaccine_type' => 'FVRCP Vaccine',
            'last_given_date' => '2023-02-10',
            'next_due_date' => '2024-02-10',
            'notes' => 'Feline viral rhinotracheitis, calicivirus, and panleukopenia vaccine.',
        ]);

       VaccineSchedule::create([
            'pet_id' => 3,
            'vaccine_type' => 'DHPP Vaccine',
            'last_given_date' => '2023-03-05',
            'next_due_date' => '2024-03-05',
            'notes' => 'Distemper, hepatitis, parvovirus, and parainfluenza vaccine.',
        ]);
    }
}
