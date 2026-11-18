<?php

namespace App\Services\Sms;

use RuntimeException;

class SmsProviderException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $providerCode = null,
        public readonly bool $retryable = false,
    ) {
        parent::__construct($message);
    }
}
