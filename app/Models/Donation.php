<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'user_id',          
        'name',             
        'last_name',        
        'email',           
        'state',
        'country',
        'zip_code',
        'amount',
        'payment_method',
        'notes',
        'anonymous',
    ];

    
    protected $casts = [
        'amount' => 'decimal:2', 
        'created_at' => 'datetime', 
        'updated_at' => 'datetime',
    ];

  
  
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }
}
