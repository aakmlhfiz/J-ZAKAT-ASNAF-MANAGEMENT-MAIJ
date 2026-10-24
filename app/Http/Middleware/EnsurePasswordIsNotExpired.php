<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('web')->user();

        if ($user && (bool) ($user->must_change_password ?? false)) {
            if (! $request->routeIs('password.request', 'password.email', 'password.reset', 'password.update', 'logout')) {
                return redirect()
                    ->route('password.request')
                    ->with('status', 'Anda perlu menetapkan kata laluan baharu sebelum meneruskan. Sila minta pautan set semula kata laluan.');
            }
        }

        return $next($request);
    }
}
