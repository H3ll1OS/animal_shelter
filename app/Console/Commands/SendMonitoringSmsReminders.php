<?php

namespace App\Console\Commands;

use App\Models\PostAdoptionVisit;
use App\Services\Sms\SmsMessageTemplates;
use App\Services\Sms\SmsNotificationService;
use Illuminate\Console\Command;

class SendMonitoringSmsReminders extends Command
{
    protected $signature = 'sms:monitoring-reminders';

    protected $description = 'Queue opted-in adopter SMS reminders for upcoming monitoring visits.';

    public function handle(SmsNotificationService $sms): int
    {
        $days = max(1, (int) config('services.sms.monitoring_reminder_days', 1));
        $reminderDate = today()->addDays($days);

        PostAdoptionVisit::query()
            ->with(['adopter', 'adoption.pet'])
            ->where('schedule_status', 'Pending')
            ->whereDate('scheduled_date', $reminderDate)
            ->orderBy('id')
            ->chunkById(100, function ($visits) use ($sms) {
                foreach ($visits as $visit) {
                    $adopter = $visit->adopter;
                    $sms->queue(
                        'monitoring:' . $visit->id . ':reminder:' . $visit->scheduled_date->format('Y-m-d'),
                        'monitoring.reminder',
                        SmsMessageTemplates::monitoringReminder($visit),
                        $adopter,
                        $adopter?->phone,
                        $visit,
                        $adopter?->sms_opted_in_at
                    );
                }
            });

        return self::SUCCESS;
    }
}
