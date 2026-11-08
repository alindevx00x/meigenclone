<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmailVerification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasVerifiedEmail() && ! $user->isAdmin()) {
            if (SiteSetting::enabled('require_email_verification')) {
                if (! $request->routeIs('verification.*') && ! $request->routeIs('logout')) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Your email address is not verified.'], 403);
                    }
                    return redirect()->route('verification.notice');
                }
            }
        }

        return $next($request);
    }
}
