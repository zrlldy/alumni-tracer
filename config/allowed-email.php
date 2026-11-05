<?php

return [
    'domains' => array_values(array_filter(array_map(
        static fn (string $domain): string => strtolower(trim($domain)),
        explode(',', (string) env('ALLOWED_EMAIL_DOMAINS', '')),
    ))),

    'prefixed_domain' => strtolower(trim(
        (string) env('ALLOWED_PREFIXED_EMAIL_DOMAIN', 'gmail.com'),
    )),

    'required_prefix' => strtolower(trim(
        (string) env('ALLOWED_EMAIL_PREFIX', 'ctu.'),
    )),
];