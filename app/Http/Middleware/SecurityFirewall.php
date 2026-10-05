<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityFirewall
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        /*
        |--------------------------------------------------------------------------
        | 1. Existing temporary block
        |--------------------------------------------------------------------------
        */
        if (Cache::has("security:blocked:{$ip}")) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $score = (int) Cache::get("security:score:{$ip}", 0);

        $path = '/' . ltrim($request->path(), '/');
        $query = $request->getQueryString() ?? '';
        $userAgent = strtolower($request->userAgent() ?? '');

        /*
        |--------------------------------------------------------------------------
        | 2. Known scanner User-Agent
        |--------------------------------------------------------------------------
        |
        | Useful for a basic ZAP demonstration.
        | Do NOT rely on this alone because ZAP allows its User-Agent
        | to be changed.
        |--------------------------------------------------------------------------
        */

        $scannerAgents = [
            'zaproxy',
            'owasp zap',
            'sqlmap',
            'nikto',
            'acunetix',
            'nessus',
            'openvas',
            'wpscan',
            'nmap',
        ];

        foreach ($scannerAgents as $scanner) {
            if (str_contains($userAgent, $scanner)) {
                $score += 100;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Suspicious scanner patterns
        |--------------------------------------------------------------------------
        */

        $patterns = [
            '/%27/i',              // '
            '/%22/i',              // "
            '/(?:%2d%2d|--)/i',   // SQL comments
            '/\/\*/i',
            '/\bunion\s+select\b/i',
            '/\bsleep\s*\(/i',
            '/\bbenchmark\s*\(/i',
            '/\bor\s+1\s*=\s*1\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (
                preg_match($pattern, $query) ||
                preg_match($pattern, $path)
            ) {
                $score += 30;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Known probe paths
        |--------------------------------------------------------------------------
        */

        $probePaths = [
            '/.env',
            '/.git/',
            '/wp-admin',
            '/wp-login.php',
            '/phpmyadmin',
            '/server-status',
            '/cgi-bin/',
            '/vendor/phpunit/',
        ];

        foreach ($probePaths as $probe) {
            if (str_starts_with($path, $probe)) {
                $score += 50;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Store score
        |--------------------------------------------------------------------------
        */

        if ($score > 0) {
            Cache::put(
                "security:score:{$ip}",
                $score,
                now()->addMinutes(10)
            );

            Log::warning('Security firewall detected suspicious request', [
                'ip' => $ip,
                'score' => $score,
                'method' => $request->method(),
                'path' => $path,
                'query' => $query,
                'user_agent' => $request->userAgent(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Temporary block
        |--------------------------------------------------------------------------
        */

        if ($score >= 100) {
            Cache::put(
                "security:blocked:{$ip}",
                true,
                now()->addMinutes(30)
            );

            Log::warning('Security firewall blocked IP', [
                'ip' => $ip,
                'score' => $score,
            ]);

            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}