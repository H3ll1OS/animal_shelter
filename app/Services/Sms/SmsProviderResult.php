<?php

namespace App\Services\Sms;

final readonly class SmsProviderResult
{
    public function __construct(
        public string $reference,
        public string $status,
    ) {}
}
