<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Cek tambahan di luar email:rfc,dns -> tolak domain contoh/sekali-pakai.
 * Catatan: ini TIDAK menjamin inbox benar-benar ada.
 */
class RealEmail implements ValidationRule
{
    private const BLOCKED = [
        'example.com', 'example.org', 'example.net', 'test.com', 'test.test', 'domain.com',
        'email.com', 'asdf.com', 'mailinator.com', 'guerrillamail.com', '10minutemail.com',
        'tempmail.com', 'temp-mail.org', 'yopmail.com', 'trashmail.com', 'sharklasers.com',
        'throwawaymail.com', 'getnada.com', 'maildrop.cc', 'dispostable.com', 'fakeinbox.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(substr(strrchr((string) $value, '@') ?: '', 1));

        if ($domain === '' || ! str_contains($domain, '.') || in_array($domain, self::BLOCKED, true)) {
            $fail('Gunakan alamat email yang valid dan aktif (bukan email sementara/contoh).');
        }
    }
}