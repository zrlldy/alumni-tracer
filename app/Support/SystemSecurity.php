<?php

namespace App\Support;

use App\Exceptions\SystemExpiredException;
use Illuminate\Support\Facades\Auth;

class SystemSecurity
{
    public static function isSystemValid(): bool
    {
        $expectedSeal = config('system.seal');

        if (blank($expectedSeal)) {
            return false;
        }

        try {
            $actualSeal = SystemEncrypter::getSeal();

            return hash_equals(
                (string) $expectedSeal,
                $actualSeal,
            );
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public static function ensureSystemValid(): void
    {
        if (! static::isSystemValid()) {
            throw new SystemExpiredException();
        }
    }

    public static function superAdminEmails(): array
    {
        $encrypted = config(
            'app.super_admin_emails_encrypted'
        );

        if (blank($encrypted)) {
            return [];
        }

        try {
            $decrypted = SystemEncrypter::decryptSuperAdminData(
                $encrypted,
            );

            return array_values(
                array_unique(
                    array_filter(
                        array_map(
                            fn (string $email): string =>
                                strtolower(trim($email)),
                            explode(',', $decrypted),
                        ),
                    ),
                ),
            );
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    public static function isBootstrapSuperAdmin(
        ?object $user = null,
    ): bool {
        $user ??= Auth::user();

        if (! $user) {
            return false;
        }

        $email = strtolower(trim((string) ($user->email ?? '')));

        if ($email === '') {
            return false;
        }

        return in_array(
            $email,
            static::superAdminEmails(),
            true,
        );
    }

    public static function developerEmails(): array
    {
        return array_values(
            array_unique([
                ...array_map(
                    fn (string $email): string =>
                        strtolower(trim($email)),
                    config('app.developer_emails', []),
                ),

                ...static::superAdminEmails(),
            ]),
        );
    }
}