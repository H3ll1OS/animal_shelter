<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function record(
        string $action,
        string $module,
        ?User $subject = null,
        array $details = [],
        ?User $actor = null,
        ?Request $request = null,
    ): ActivityLog {
        $actor ??= auth()->user();
        $request ??= request();

        return ActivityLog::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'actor_email' => $actor?->email,
            'action' => $action,
            'module' => $module,
            'subject_user_id' => $subject?->id,
            'subject_name' => $subject?->name,
            'subject_email' => $subject?->email,
            'details' => $details,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
