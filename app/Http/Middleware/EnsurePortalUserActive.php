<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('portal')->user();

        if ($user && ! $user->isActive()) {
            auth('portal')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('user.login')->withErrors([
                'email' => 'Akaun anda tidak aktif. Sila hubungi pihak MAIJ.',
            ]);
        }

        return $next($request);
    }
}
