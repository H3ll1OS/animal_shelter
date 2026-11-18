<?php

namespace Database\Seeders;

use App\Models\EuthanasiaLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EuthanasiaLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $pet = Pet::where('name', 'Sadie')->where('status', 'Euthanized')->first();
        $user = User::where('role', 'admin')->orderBy('id')->first();

        if (! $pet || ! $user) return;

        EuthanasiaLog::firstOrCreate([
            'pet_id' => $pet->id,
            'euthanasia_date' => '2023-11-15 16:00:00',
        ], [
            'reason' => 'Severe illness with no recovery prospects',
            'user_id' => $user->id,
            'notes' => 'Performed humanely by licensed veterinarian.',
        ]);
    }
}
