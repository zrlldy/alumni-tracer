<?php

namespace App\Support;

use Illuminate\Http\Request;

class ClientHints
{
    /**
     * Pull whatever Client Hints the browser sent on this request.
     * Returns an empty-valued array if the browser didn't send them
     * (e.g. Firefox/Safari, or the very first request in a session).
     */
    public static function capture(Request $request): array
    {
        return [
            'model' => self::clean($request->header('Sec-CH-UA-Model')),
            'platform' => self::clean($request->header('Sec-CH-UA-Platform')),
            'platformVersion' => self::clean($request->header('Sec-CH-UA-Platform-Version')),
            'mobile' => $request->header('Sec-CH-UA-Mobile') === '?1',
        ];
    }

    protected static function clean(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        // Client Hint header values arrive wrapped in double quotes, e.g. "V2244".
        return trim($value, " \t\n\r\0\x0B\"");
    }

    public static function rememberInSession(\Illuminate\Http\Request $request): void
    {
        $hints = self::capture($request);

        session([
            'ch_model' => $hints['model'],
            'ch_platform' => $hints['platform'],
            'ch_platform_version' => $hints['platformVersion'],
        ]);
    }
}