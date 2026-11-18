<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAdoptionVisitAttachment extends Model
{
    protected $fillable = ['file_path', 'original_filename'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PostAdoptionVisit::class, 'post_adoption_visit_id');
    }
}
