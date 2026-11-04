<?php

namespace App\Support;

class SessionPayloadReader
{
    /**
     * Decode a row's `payload` column from the `sessions` table and pull
     * out the client-hint values we stashed in the session during login.
     *
     * Only works when SESSION_ENCRYPT=false (Laravel's default). If you've
     * turned session encryption on, this needs an extra Crypt::decrypt()
     * step — ping me if that's the case and I'll adjust it.
     */
    public static function readHints(?string $payload): array
    {
        $empty = ['model' => null, 'platform' => null, 'platformVersion' => null];

        if (blank($payload)) {
            return $empty;
        }

        try {
            $decoded = base64_decode($payload, true);

            if ($decoded === false) {
                return $empty;
            }

            $data = @unserialize($decoded);

            if (! is_array($data)) {
                return $empty;
            }

            return [
                'model' => $data['ch_model'] ?? null,
                'platform' => $data['ch_platform'] ?? null,
                'platformVersion' => $data['ch_platform_version'] ?? null,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }
}