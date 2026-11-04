<?php

namespace App\Support;

class AllowedEmailDomains
{
    public static function passes(string $email): bool
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        [$localPart, $domain] = explode('@', $email, 2);

        $allowedDomains = [
            'gmail.com',
            'joycodes.org',
            'portunite.com',
        ];

        return in_array($domain, $allowedDomains, true)
            || (
                $domain === 'gmail.com'
                && str_starts_with($localPart, 'ctu.')
            );
    }
}