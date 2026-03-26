<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\VaccineSchedule;
use App\Models\EuthanasiaLog;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    use HasFactory;
 

    protected $fillable = [
        'name', 'species', 'breed', 'age', 'gender', 'description', 
        'image_path', 'status', 'date_received', 'size'
    ];

    protected $casts = [
        'date_received' => 'date',
    ];

   
    public function adoptions()
    {
        return $this->hasMany(Adoption::class);
    }
    
    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function vaccineSchedules(): HasMany
    {
        return $this->hasMany(VaccineSchedule::class, 'pet_id');
    }

    public function euthanasiaLogs(): HasMany
    {
        return $this->hasMany(EuthanasiaLog::class, 'pet_id');
    }
}
