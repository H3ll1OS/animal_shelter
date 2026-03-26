<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;
    
   
    protected $table = 'health_records'; 

    protected $fillable = [
        'pet_id',
        'diagnosis',
        'description',
        'is_critical',
        'reported_date',
        'veterinarian',
        'notes',
    ];
    
  
    public function pet()
    {
        return $this->belongsTo(Pet::class); 
    }
}