<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RepairFilamentAlpineExpressions
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        $contentType = strtolower(
            (string) $response->headers->get('Content-Type')
        );

        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();

        if (! is_string($html) || $html === '') {
            return $response;
        }

        /*
        |--------------------------------------------------------------------------
        | Filament theme switcher
        |--------------------------------------------------------------------------
        |
        | Html minification can remove the statement boundary before:
        |
        | theme = localStorage.getItem('theme') || 'system'
        |
        */

        $html = str_replace(
            ") theme = localStorage.getItem('theme') || 'system'",
            ");theme = localStorage.getItem('theme') || 'system'",
            $html,
        );

        $html = preg_replace(
            '/(\$dispatch\([\'"]theme-changed[\'"]\s*,\s*theme\)\s*\})\s+theme\s*=/',
            '$1;theme=',
            $html,
        ) ?? $html;


        /*
        |--------------------------------------------------------------------------
        | Filament Select / Alpine
        |--------------------------------------------------------------------------
        |
        | Html minification can transform:
        |
        |     const container = $el.nextElementSibling
        |     container.dispatchEvent(...)
        |
        | into:
        |
        |     const container = $el.nextElementSibling container.dispatchEvent(...)
        |
        | Restore the missing statement boundary.
        |
        */

        $html = preg_replace(
            '/(\$el\.nextElementSibling)\s+(container\.dispatchEvent\s*\()/',
            '$1;$2',
            $html,
        ) ?? $html;


        $response->setContent($html);

        return $response;
    }
}