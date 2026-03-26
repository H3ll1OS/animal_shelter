<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adoption extends Model
{
    protected $fillable = [
        'pet_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'message',
        'document_path',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
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
