<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsNotification extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_name',
        'recipient_phone',
        'notification_type',
        'related_type',
        'related_id',
        'message',
        'provider',
        'provider_reference',
        'provider_status',
        'status',
        'error_code',
        'error_message',
        'retryable',
        'attempt_count',
        'consent_at',
        'sent_at',
        'delivered_at',
        'dedupe_key',
    ];

    protected $casts = [
        'retryable' => 'boolean',
        'consent_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'related_type', 'related_id');
    }
}
