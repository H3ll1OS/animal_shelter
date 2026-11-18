<?php

namespace App\Services\Sms;

use App\Models\SmsNotification;

interface SmsProvider
{
    public function send(SmsNotification $notification): SmsProviderResult;
}
