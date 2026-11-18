<?php

namespace App\Services\Sms;

class PhilippineMobileNumber
{
    public static function normalize(?string $number): ?string
    {
        if (! is_string($number)) {
            return null;
        }

        $number = trim($number);
        $digits = preg_replace('/\D+/', '', $number);

        if (preg_match('/^09\d{9}$/', $digits)) {
            return '+63' . substr($digits, 1);
        }

        if (preg_match('/^639\d{9}$/', $digits)) {
            return '+' . $digits;
        }

        return null;
    }

    public static function safeDisplay(?string $number): ?string
    {
        if (! is_string($number) || trim($number) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        return $digits === '' ? null : $digits;
    }
}
