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
        'receipt_path',
        'notes',
        'anonymous',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'rejected_at',
    ];

    protected $hidden = [
        'receipt_path',
    ];

    protected $appends = [
        'has_receipt',
    ];

    
    protected $casts = [
        'amount' => 'decimal:2', 
        'admin_seen_at' => 'datetime',
        'created_at' => 'datetime', 
        'updated_at' => 'datetime',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

  
  
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }

    public function getHasReceiptAttribute(): bool
    {
        return filled($this->receipt_path);
    }
}
