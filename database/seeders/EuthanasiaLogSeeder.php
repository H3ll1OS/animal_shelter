<?php

namespace Database\Seeders;

use App\Models\EuthanasiaLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EuthanasiaLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        EuthanasiaLog::create([
            'pet_id' => 4, 
            'reason' => 'Severe illness with no recovery prospects',
            'euthanasia_date' => '2023-11-15 16:00:00',
            'user_id' => 1,
            'notes' => 'Performed humanely by licensed veterinarian.',
        ]);
    }
}
