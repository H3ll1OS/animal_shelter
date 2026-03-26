<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EuthanasiaLog extends Model
{
    protected $fillable = [
        'pet_id',
        'reason',
        'euthanasia_date',
        'user_id',
        'notes',
        'veterinarian',
    ];

    protected $casts = [
        'euthanasia_date' => 'datetime',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
