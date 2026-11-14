<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('filament.online.auth.logout', 'filament.online.auth.email-verification.*')) {
            return $next($request);
        }

        /** @var User|null $user */
        $user = $request->user()?->fresh();

        if ($user && ! $user->hasAcceptedCurrentTerms()) {
            return redirect()->route('terms.accept');
        }

        return $next($request);
    }
}
