<?php

namespace App\Support;

use Illuminate\Encryption\Encrypter;
use RuntimeException;

class SystemEncrypter
{
    protected static function systemKeyEncrypter(): Encrypter
    {
        $systemKey = config('app.system_key');

        if (blank($systemKey)) {
            throw new RuntimeException(
                'SYSTEM_KEY is not configured.'
            );
        }

        $key = hash(
            'sha256',
            $systemKey,
            true,
        );

        return new Encrypter(
            $key,
            'AES-256-CBC',
        );
    }

    protected static function superAdminEmailsCiphertext(): string
    {
        $value = config('app.super_admin_emails_encrypted');

        if (blank($value)) {
            throw new RuntimeException(
                'SUPER_ADMIN_EMAILS is not configured.'
            );
        }

        return (string) $value;
    }

    protected static function systemKey(): string
    {
        $key = config('app.system_key');

        if (blank($key)) {
            throw new RuntimeException(
                'SYSTEM_KEY is not configured.'
            );
        }

        return (string) $key;
    }

    protected static function seal(): string
    {
        return hash(
            'sha256',
            static::systemKey()
                . '|'
                . static::superAdminEmailsCiphertext(),
            true,
        );
    }

    protected static function masterEncrypter(): Encrypter
    {
        return new Encrypter(
            static::seal(),
            'AES-256-CBC',
        );
    }

    public static function encryptSuperAdminData(
        string $value,
    ): string {
        return static::systemKeyEncrypter()
            ->encryptString($value);
    }

    public static function decryptSuperAdminData(
        string $value,
    ): string {
        return static::systemKeyEncrypter()
            ->decryptString($value);
    }

    public static function encrypt(string $value): string
    {
        return static::masterEncrypter()
            ->encryptString($value);
    }

    public static function decrypt(string $value): string
    {
        return static::masterEncrypter()
            ->decryptString($value);
    }

    public static function getSeal(): string
    {
        return base64_encode(
            static::seal(),
        );
    }
}