<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'actor_id', 'actor_name', 'actor_email', 'action', 'module',
        'subject_user_id', 'subject_name', 'subject_email', 'details',
        'ip_address', 'user_agent',
    ];

    protected $casts = ['details' => 'array'];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
