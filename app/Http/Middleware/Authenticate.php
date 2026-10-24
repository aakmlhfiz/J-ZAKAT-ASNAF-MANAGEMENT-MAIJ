<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        if (! $request->expectsJson()) {
            return redirect()->route($this->loginRouteFor($guards));
        }

        abort(401);
    }

    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            return route($this->loginRouteFor());
        }

        return null;
    }

    private function loginRouteFor(array $guards = []): string
    {
        $guards = empty($guards) ? [auth()->getDefaultDriver()] : $guards;

        if (in_array('portal', $guards, true) && ! in_array('web', $guards, true)) {
            return 'user.login';
        }

        return 'login';
    }
}
