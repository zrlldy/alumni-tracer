<?php

namespace App\Support;

class Terms
{
    public const VERSION = '2026-12-v1';

    /** @return list<array{type: string, text: string}> */
    public static function document(): array
    {
        return json_decode(file_get_contents(resource_path('legal/terms-2026-12.json')), true, flags: JSON_THROW_ON_ERROR);
    }
}
