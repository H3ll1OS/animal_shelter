<?php

namespace App\Services\Sms;

use App\Models\SmsNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class TwilioSmsProvider implements SmsProvider
{
    public function send(SmsNotification $notification): SmsProviderResult
    {
        $accountSid = (string) config('services.sms.twilio.account_sid');
        $authToken = (string) config('services.sms.twilio.auth_token');
        $messagingServiceSid = (string) config('services.sms.twilio.messaging_service_sid');
        $from = (string) config('services.sms.twilio.from');

        if ($accountSid === '' || $authToken === '' || ($messagingServiceSid === '' && $from === '')) {
            throw new SmsProviderException('Twilio credentials or sender configuration are missing.', 'configuration_missing');
        }

        $payload = [
            'To' => $notification->recipient_phone,
            'Body' => $notification->message,
            'StatusCallback' => URL::route('api.sms.twilio.status'),
        ];

        if ($messagingServiceSid !== '') {
            $payload['MessagingServiceSid'] = $messagingServiceSid;
        } else {
            $payload['From'] = $from;
        }

        $response = Http::asForm()
            ->withBasicAuth($accountSid, $authToken)
            ->timeout((int) config('services.sms.timeout', 10))
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", $payload);

        if ($response->status() === 429) {
            throw new SmsProviderException(
                'Twilio rate limited this SMS request.',
                (string) ($response->json('code') ?? 'rate_limited'),
                true
            );
        }

        if (! $response->successful()) {
            throw new SmsProviderException(
                'Twilio rejected the SMS request (HTTP ' . $response->status() . ').',
                (string) ($response->json('code') ?? 'provider_rejected')
            );
        }

        $reference = $response->json('sid');
        $status = $response->json('status');

        if (! is_string($reference) || $reference === '' || ! is_string($status) || $status === '') {
            throw new SmsProviderException('Twilio returned an incomplete message response.', 'invalid_provider_response');
        }

        return new SmsProviderResult($reference, $status);
    }
}
