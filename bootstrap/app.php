<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsurePasswordIsNotExpired;
use App\Http\Middleware\EnsurePortalUserActive;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\SetLocale;
use App\Services\FirebaseService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'password.not_expired' => EnsurePasswordIsNotExpired::class,
            'super.admin' => EnsureSuperAdmin::class,
            'portal.active' => EnsurePortalUserActive::class,
        ]);

        $middleware->appendToGroup('web', [
            SetLocale::class,
            EnsurePasswordIsNotExpired::class,
        ]);
    })
    ->withSingletons([
        FirebaseService::class => fn () => new FirebaseService(
            config('services.firebase.api_key'),
        ),
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
