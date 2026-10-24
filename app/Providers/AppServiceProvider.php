<?php

namespace App\Providers;

use App\Services\Ai\AiService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiService::class);
    }

    public function boot(): void
    {
        $this->stripLegacyUsersProvider();

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(10)->by($request->input('email', $request->ip())));
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(3)->by($request->input('email', $request->ip())));
        RateLimiter::for('ai-chat', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('import', fn (Request $request) => Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('pdf-upload', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
    }

    /**
     * Laravel's framework config bootstrapper merges vendor/.../config/auth.php
     * into the project auth config, which adds a default `users` provider and
     * `users` broker pointing at the now-deleted App\Models\User class. Strip
     * them so the merged config contains only the two real providers/brokers
     * defined in config/auth.php.
     */
    private function stripLegacyUsersProvider(): void
    {
        $providers = config('auth.providers', []);
        $passwords = config('auth.passwords', []);

        unset($providers['users']);
        unset($passwords['users']);

        config([
            'auth.providers' => $providers,
            'auth.passwords' => $passwords,
        ]);
    }
}
