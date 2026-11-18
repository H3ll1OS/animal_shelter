<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostAdoptionVisit extends Model
{
    protected $fillable = [
        'adoption_id', 'pet_id', 'adopter_id', 'assigned_user_id', 'created_by', 'completed_by',
        'scheduled_date', 'actual_visit_date', 'monitoring_method', 'purpose', 'schedule_status',
        'pet_status', 'health_condition', 'living_condition', 'ownership_status', 'findings',
        'recommendations', 'notes', 'next_follow_up_date',
    ];

    protected $casts = [
        'scheduled_date' => 'date:Y-m-d',
        'actual_visit_date' => 'date:Y-m-d',
        'next_follow_up_date' => 'date:Y-m-d',
    ];

    public function adoption(): BelongsTo { return $this->belongsTo(Adoption::class); }
    public function pet(): BelongsTo { return $this->belongsTo(Pet::class); }
    public function adopter(): BelongsTo { return $this->belongsTo(User::class, 'adopter_id'); }
    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_user_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function completer(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }
    public function attachments(): HasMany { return $this->hasMany(PostAdoptionVisitAttachment::class); }
}
