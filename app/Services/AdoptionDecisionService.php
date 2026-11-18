<?php

namespace App\Services;

use App\Models\Adoption;
use App\Models\Pet;
use App\Services\Sms\SmsMessageTemplates;
use App\Services\Sms\SmsNotificationService;
use Illuminate\Support\Facades\DB;

class AdoptionDecisionService
{
    public function approve(Adoption $adoption): string
    {
        $result = DB::transaction(function () use ($adoption) {
            $lockedAdoption = Adoption::query()->lockForUpdate()->findOrFail($adoption->id);
            if ($lockedAdoption->status !== 'Pending') {
                return 'not_pending';
            }

            $pet = Pet::query()->lockForUpdate()->findOrFail($lockedAdoption->pet_id);
            if ($pet->status !== 'Available' || $pet->euthanasiaLogs()->exists()) {
                return 'pet_unavailable';
            }

            $lockedAdoption->update([
                'status' => 'Approved',
                'decision_seen_at' => null,
            ]);
            $pet->update(['status' => 'Adopted']);

            return 'approved';
        });

        if ($result === 'approved') {
            $this->queueDecisionNotification($adoption->fresh(), true);
        }

        return $result;
    }

    public function reject(Adoption $adoption): string
    {
        $result = DB::transaction(function () use ($adoption) {
            $lockedAdoption = Adoption::query()->lockForUpdate()->findOrFail($adoption->id);
            if ($lockedAdoption->status !== 'Pending') {
                return 'not_pending';
            }

            $lockedAdoption->update([
                'status' => 'Rejected',
                'decision_seen_at' => null,
            ]);

            return 'rejected';
        });

        if ($result === 'rejected') {
            $this->queueDecisionNotification($adoption->fresh(), false);
        }

        return $result;
    }

    private function queueDecisionNotification(Adoption $adoption, bool $approved): void
    {
        $adoption->loadMissing('user', 'pet');
        $user = $adoption->user;
        $status = $approved ? 'approved' : 'rejected';

        app(SmsNotificationService::class)->queue(
            'adoption:'.$adoption->id.':'.$status,
            'adoption.'.$status,
            SmsMessageTemplates::applicationDecision($adoption, $approved),
            $user,
            $user?->phone,
            $adoption,
            $user?->sms_opted_in_at
        );
    }
}
