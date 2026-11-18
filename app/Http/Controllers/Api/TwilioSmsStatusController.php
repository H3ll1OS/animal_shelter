<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class TwilioSmsStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        $authToken = (string) config('services.sms.twilio.auth_token');
        abort_if($authToken === '', 503, 'SMS status callback is not configured.');

        $signature = (string) $request->header('X-Twilio-Signature');
        abort_if($signature === '', 403);

        $fields = $request->post();
        abort_unless(
            hash_equals((string) config('services.sms.twilio.account_sid'), (string) $request->input('AccountSid')),
            403
        );
        ksort($fields);
        $signedPayload = URL::route('api.sms.twilio.status');
        foreach ($fields as $key => $value) {
            $signedPayload .= $key . (is_scalar($value) ? (string) $value : '');
        }

        $expected = base64_encode(hash_hmac('sha1', $signedPayload, $authToken, true));
        abort_unless(hash_equals($expected, $signature), 403);

        $reference = $request->input('MessageSid', $request->input('SmsSid'));
        $providerStatus = strtolower((string) $request->input('MessageStatus', $request->input('SmsStatus')));
        abort_unless(is_string($reference) && preg_match('/^(SM|MM)[0-9a-fA-F]{32}$/', $reference), 422);
        abort_unless(in_array($providerStatus, ['accepted', 'scheduled', 'canceled', 'queued', 'sending', 'sent', 'delivered', 'failed', 'undelivered'], true), 422);

        $notification = SmsNotification::query()
            ->where('provider_reference', $reference)
            ->where('provider', 'twilio')
            ->first();

        if ($notification) {
            if (in_array($notification->status, ['delivered', 'failed'], true) && $providerStatus !== 'delivered') {
                return response()->noContent();
            }

            $changes = ['provider_status' => $providerStatus];

            if ($providerStatus === 'delivered') {
                $changes['status'] = 'delivered';
                $changes['delivered_at'] = $notification->delivered_at ?? now();
                $changes['sent_at'] = $notification->sent_at ?? now();
            } elseif ($providerStatus === 'sent' && $notification->status === 'accepted') {
                $changes['status'] = 'sent';
                $changes['sent_at'] = $notification->sent_at ?? now();
            } elseif (in_array($providerStatus, ['failed', 'undelivered', 'canceled'], true) && $notification->status !== 'delivered') {
                $changes['status'] = 'failed';
                $changes['retryable'] = false;
                $changes['error_code'] = $request->input('ErrorCode')
                    ? substr((string) $request->input('ErrorCode'), 0, 80)
                    : 'delivery_failed';
                $changes['error_message'] = 'The provider reported that the SMS was not delivered.';
            }

            $notification->forceFill($changes)->save();
        }

        return response()->noContent();
    }
}
