<?php

// app/Support/TrustedDeviceCookie.php
namespace App\Support;

use App\Models\TrustedDevice;

class TrustedDeviceCookie
{
    public static function prune(array $map): array
    {
        if (empty($map)) {
            return $map;
        }

        $hashedTokensByUserId = [];

        foreach ($map as $userId => $token) {
            $hashedTokensByUserId[$userId] = hash('sha256', $token);
        }

        $validPairs = TrustedDevice::query()
            ->whereIn('user_id', array_keys($hashedTokensByUserId))
            ->where('expires_at', '>', now())
            ->get(['user_id', 'token'])
            ->map(fn ($row) => $row->user_id . ':' . $row->token)
            ->all();

        return array_filter(
            $map,
            fn ($token, $userId) => in_array($userId . ':' . $hashedTokensByUserId[$userId], $validPairs, true),
            ARRAY_FILTER_USE_BOTH,
        );
    }
}