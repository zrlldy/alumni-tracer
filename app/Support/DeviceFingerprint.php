<?php
namespace App\Support;

use Illuminate\Http\Request;

class DeviceFingerprint
{
    public static function compute(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
            $request->header('Sec-CH-UA'),
            $request->header('Sec-CH-UA-Platform'),
            $request->header('Sec-CH-UA-Mobile'),
        ];

        return hash(
            'sha256',
            implode('|', $components)
        );
    }
}