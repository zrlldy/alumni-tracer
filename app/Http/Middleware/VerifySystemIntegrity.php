<?php

namespace App\Http\Middleware;

use App\Support\SystemSecurity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySystemIntegrity
{
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        SystemSecurity::ensureSystemValid();

        return $next($request);
    }
}