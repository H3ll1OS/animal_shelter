<?php

namespace App\Jobs;

use App\Models\SmsNotification;
use App\Services\Sms\SmsProviderException;
use App\Services\Sms\SmsProvider;
use App\Services\Sms\TwilioSmsProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

class SendSmsNotification implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 20;

    public function __construct(public readonly int $notificationId) {}

    public function uniqueId(): string
    {
        return (string) $this->notificationId;
    }

    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(): void
    {
        $notification = SmsNotification::query()->find($this->notificationId);
        if (! $notification || $notification->status !== 'pending') {
            return;
        }

        $notification->loadMissing('user');
        if ($notification->user_id === null || ! $this->consentIsCurrent($notification)) {
            $notification->forceFill([
                'status' => 'skipped',
                'error_code' => $notification->user_id === null ? 'recipient_missing' : 'consent_revoked',
                'error_message' => $notification->user_id === null
                    ? 'A registered recipient is no longer associated with this SMS.'
                    : 'The recipient no longer has the SMS opt-in recorded for this notification.',
            ])->save();

            return;
        }

        $notification->increment('attempt_count');

        try {
            $provider = $this->provider();
            $result = $provider->send($notification);
        } catch (SmsProviderException $exception) {
            $notification->forceFill([
                'error_code' => $exception->providerCode,
                'error_message' => $exception->getMessage(),
                'retryable' => $exception->retryable,
            ])->save();

            if ($exception->retryable) {
                throw $exception;
            }

            $notification->forceFill(['status' => 'failed'])->save();

            return;
        } catch (ConnectionException $exception) {
            $notification->forceFill([
                'status' => 'failed',
                'error_code' => 'connection_error',
                'error_message' => 'The provider connection failed before a delivery result was confirmed.',
                'retryable' => false,
            ])->save();
            report($exception);

            return;
        }

        $status = in_array($result->status, ['failed', 'undelivered'], true)
            ? 'failed'
            : ($result->status === 'sent' ? 'sent' : 'accepted');

        $notification->forceFill([
            'provider' => (string) config('services.sms.provider'),
            'provider_reference' => $result->reference,
            'provider_status' => $result->status,
            'status' => $status,
            'sent_at' => $status === 'sent' ? now() : $notification->sent_at,
            'retryable' => false,
            'error_code' => null,
            'error_message' => null,
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        $notification = SmsNotification::query()->find($this->notificationId);
        if (! $notification || $notification->status !== 'pending') {
            return;
        }

        $notification->forceFill([
            'status' => 'failed',
            'error_code' => $exception instanceof SmsProviderException
                ? ($exception->providerCode ?? 'provider_error')
                : 'queue_failed',
            'error_message' => $exception instanceof SmsProviderException
                ? $exception->getMessage()
                : 'SMS delivery failed after the configured queue attempts.',
            'retryable' => $exception instanceof SmsProviderException && $exception->retryable,
        ])->save();
    }

    private function provider(): SmsProvider
    {
        return match (config('services.sms.provider')) {
            'twilio' => app(TwilioSmsProvider::class),
            default => throw new SmsProviderException('The configured SMS provider is not supported.', 'provider_unsupported'),
        };
    }

    private function consentIsCurrent(SmsNotification $notification): bool
    {
        $currentConsent = $notification->user?->sms_opted_in_at;

        return $currentConsent !== null
            && $notification->consent_at !== null
            && $currentConsent->format('U') === $notification->consent_at->format('U');
    }
}
