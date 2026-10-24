<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('web')->user();

        $emails = (array) config('app.super_admin_emails');
        if (! $user || ! in_array($user->email, $emails, true)) {
            abort(403, 'Akses terhad kepada Super Admin sahaja.');
        }

        return $next($request);
    }
}
