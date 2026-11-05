<?php

namespace App\Support;

class AllowedEmailDomains
{
    /**
     * Determine whether an email address is allowed to register or sign in.
     * Configured bootstrap Super Admin email addresses are exempt from the
     * normal domain allowlist, but must still be valid email addresses.
     */
    public static function passes(string $email): bool
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (self::isSuperAdminEmail($email)) {
            return true;
        }

        [$localPart, $domain] = explode('@', $email, 2);

        $prefixedDomain = strtolower(trim(
            (string) config('allowed-email.prefixed_domain', '')
        ));

        $requiredPrefix = strtolower(trim(
            (string) config('allowed-email.required_prefix', '')
        ));

        // A prefixed domain always requires the configured local-part prefix,
        // even if that domain also appears in the general allowlist.
        if ($prefixedDomain !== '' && $domain === $prefixedDomain) {
            return $requiredPrefix !== ''
                && str_starts_with($localPart, $requiredPrefix)
                && strlen($localPart) > strlen($requiredPrefix);
        }

        $allowedDomains = array_map(
            static fn ($allowedDomain): string => strtolower(trim((string) $allowedDomain)),
            (array) config('allowed-email.domains', [])
        );

        return in_array($domain, $allowedDomains, true);
    }

    /**
     * Determine whether the exact email is configured as a bootstrap
     * Super Admin by SystemSecurity.
     */
    public static function isSuperAdminEmail(string $email): bool
    {
        $email = strtolower(trim($email));

        $superAdminEmails = array_map(
            static fn ($adminEmail): string => strtolower(trim((string) $adminEmail)),
            (array) SystemSecurity::superAdminEmails()
        );

        return in_array($email, $superAdminEmails, true);
    }
}
