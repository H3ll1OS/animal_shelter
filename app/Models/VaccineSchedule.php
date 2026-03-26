<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccineSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vaccine_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
        protected $fillable = [
        'pet_id',
        'vaccine_type',   
        'vaccination_date',
        'last_given_date',
        'next_due_date',
        'veterinarian',
        'notes',
        'is_completed',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
    
        'vaccination_date' => 'date', 
        'next_due_date' => 'date',
        'last_given_date' => 'date',
        'is_completed' => 'boolean',
    ];

    
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }
}