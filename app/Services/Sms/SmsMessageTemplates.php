<?php

namespace App\Services\Sms;

use App\Models\Adoption;
use App\Models\PostAdoptionVisit;
use Illuminate\Support\Str;

class SmsMessageTemplates
{
    public static function applicationSubmitted(Adoption $adoption): string
    {
        return sprintf(
            'Hi %s, we received your adoption application for %s. It is awaiting review. Thank you!',
            self::name($adoption->user?->name ?: $adoption->full_name),
            self::petName($adoption)
        );
    }

    public static function applicationDecision(Adoption $adoption, bool $approved): string
    {
        if ($approved) {
            return sprintf(
                'Hi %s, your application for %s is approved. Contact the Animal Shelter for next steps. Thank you!',
                self::name($adoption->user?->name ?: $adoption->full_name),
                self::petName($adoption)
            );
        }

        return sprintf(
            'Hi %s, your application for %s was not approved. Thank you for your interest in adopting.',
            self::name($adoption->user?->name ?: $adoption->full_name),
            self::petName($adoption)
        );
    }

    public static function monitoringScheduled(PostAdoptionVisit $visit, bool $updated = false): string
    {
        $action = $updated ? 'updated' : 'scheduled';
        $date = $visit->scheduled_date?->format('M j, Y') ?? 'the scheduled date';

        return sprintf(
            'Hi %s, your monitoring visit for %s is %s for %s. Contact the Animal Shelter if you need help.',
            self::name($visit->adopter?->name),
            self::petName($visit->adoption),
            $action,
            $date
        );
    }

    public static function monitoringReminder(PostAdoptionVisit $visit): string
    {
        $date = $visit->scheduled_date?->format('M j, Y') ?? 'the scheduled date';

        return sprintf(
            'Reminder, %s: monitoring for %s is scheduled for %s. Contact the Animal Shelter if you need help.',
            self::name($visit->adopter?->name),
            self::petName($visit->adoption),
            $date
        );
    }

    private static function petName(?Adoption $adoption): string
    {
        $name = trim((string) $adoption?->pet?->name);

        return $name === '' ? 'your pet' : self::compactText($name, 30);
    }

    private static function name(?string $name): string
    {
        $name = trim((string) $name);

        return $name === '' ? 'there' : self::compactText($name, 24);
    }

    private static function compactText(string $value, int $limit): string
    {
        $value = preg_replace('/[\r\n\t]+/u', ' ', trim($value)) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return Str::limit($value, $limit, '');
    }
}
