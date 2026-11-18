<?php

namespace App\Services\Sms;

use App\Jobs\SendSmsNotification;
use App\Models\SmsNotification;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

class SmsNotificationService
{
    public function queue(
        string $dedupeKey,
        string $type,
        string $message,
        ?User $recipient,
        ?string $phone,
        ?Model $related,
        ?Carbon $consentAt
    ): ?SmsNotification {
        $normalizedPhone = PhilippineMobileNumber::normalize($phone);
        $skipReason = $this->skipReason($normalizedPhone, $consentAt, $recipient);
        $provider = (string) config('services.sms.provider', 'twilio');

        try {
            $notification = SmsNotification::query()->firstOrCreate(
                ['dedupe_key' => $dedupeKey],
                [
                    'user_id' => $recipient?->id,
                    'recipient_name' => $recipient?->name,
                    'recipient_phone' => $normalizedPhone ?? PhilippineMobileNumber::safeDisplay($phone),
                    'notification_type' => $type,
                    'related_type' => $related?->getMorphClass(),
                    'related_id' => $related?->getKey(),
                    'message' => $message,
                    'provider' => $provider ?: null,
                    'status' => $skipReason === null ? 'pending' : 'skipped',
                    'error_code' => $skipReason,
                    'error_message' => $skipReason ? $this->reasonMessage($skipReason) : null,
                    'consent_at' => $consentAt,
                ]
            );
        } catch (QueryException $exception) {
            try {
                $notification = SmsNotification::query()->where('dedupe_key', $dedupeKey)->first();
                if ($notification) {
                    return $notification;
                }
            } catch (Throwable $lookupException) {
                report($lookupException);
            }

            report($exception);

            return null;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        if (! $notification->wasRecentlyCreated || $notification->status !== 'pending') {
            return $notification;
        }

        try {
            SendSmsNotification::dispatch($notification->id)->afterCommit();
        } catch (Throwable $exception) {
            $notification->forceFill([
                'status' => 'failed',
                'error_code' => 'queue_unavailable',
                'error_message' => 'The SMS could not be added to the delivery queue.',
                'retryable' => true,
            ])->save();
            report($exception);
        }

        return $notification;
    }

    public function retry(SmsNotification $notification): bool
    {
        if ($notification->status !== 'failed' || ! $notification->retryable || $notification->provider_reference !== null) {
            return false;
        }

        $notification->loadMissing('user', 'related');
        if (! $this->stillConsented($notification)) {
            $notification->forceFill([
                'status' => 'skipped',
                'retryable' => false,
                'error_code' => 'consent_revoked',
                'error_message' => 'The recipient no longer has an active SMS opt-in.',
            ])->save();

            return false;
        }

        $notification->forceFill([
            'status' => 'pending',
            'error_code' => null,
            'error_message' => null,
            'retryable' => false,
        ])->save();

        try {
            SendSmsNotification::dispatch($notification->id)->afterCommit();
        } catch (Throwable $exception) {
            $notification->forceFill([
                'status' => 'failed',
                'error_code' => 'queue_unavailable',
                'error_message' => 'The SMS could not be added to the delivery queue.',
                'retryable' => true,
            ])->save();
            report($exception);

            return false;
        }

        return true;
    }

    private function skipReason(?string $normalizedPhone, ?Carbon $consentAt, ?User $recipient): ?string
    {
        if ($recipient === null) {
            return 'recipient_missing';
        }

        if ($consentAt === null) {
            return 'consent_missing';
        }

        if ($normalizedPhone === null) {
            return 'invalid_mobile_number';
        }

        $provider = (string) config('services.sms.provider', '');
        if ($provider !== 'twilio') {
            return 'provider_not_configured';
        }

        $configured = filled(config('services.sms.twilio.account_sid'))
            && filled(config('services.sms.twilio.auth_token'))
            && (filled(config('services.sms.twilio.messaging_service_sid')) || filled(config('services.sms.twilio.from')));

        return $configured ? null : 'provider_not_configured';
    }

    private function stillConsented(SmsNotification $notification): bool
    {
        if ($notification->user_id !== null) {
            $currentConsent = $notification->user?->sms_opted_in_at;

            return $currentConsent !== null
                && $notification->consent_at !== null
                && $currentConsent->format('U') === $notification->consent_at->format('U');
        }

        return false;
    }

    private function reasonMessage(string $reason): string
    {
        return match ($reason) {
            'recipient_missing' => 'No registered recipient is associated with this notification.',
            'consent_missing' => 'No explicit SMS opt-in was recorded.',
            'invalid_mobile_number' => 'The recipient mobile number is not a valid Philippine mobile number.',
            default => 'The SMS provider is not configured.',
        };
    }
}
