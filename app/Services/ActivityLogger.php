<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\LoginLog;
use App\Models\SecurityLog;
use App\Services\Concerns\ParsesUserAgent;
use Illuminate\Http\Request;

class ActivityLogger
{
    use ParsesUserAgent;

    public static function login($user, Request $request): void
    {
        $agent = (new self)->parseAgent($request);

        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'location' => (new self)->resolveLocation($request),
            'status' => 'success',
            'user_agent' => $request->userAgent(),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'login',
            'description' => __('audit.activity.login'),
            'device' => $agent['device'],
            'browser' => $agent['browser'].' · '.$agent['os'],
            'ip_address' => $request->ip(),
            'location' => (new self)->resolveLocation($request),
        ]);
    }

    public static function logout($user, Request $request): void
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'logout',
            'description' => __('audit.activity.logout'),
            'ip_address' => $request->ip(),
        ]);
    }

    public static function failedLogin($email, Request $request): void
    {
        $agent = (new self)->parseAgent($request);

        LoginLog::create([
            'user_id' => null,
            'ip_address' => $request->ip(),
            'browser' => $agent['browser'],
            'device' => $agent['device'],
            'os' => $agent['os'],
            'location' => (new self)->resolveLocation($request),
            'status' => 'failed',
            'user_agent' => $request->userAgent(),
        ]);
    }

    public static function profileUpdated($user, array $changes, Request $request): void
    {
        $agent = (new self)->parseAgent($request);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'profile_update',
            'description' => __('audit.activity.profile_updated').' ('.implode(', ', array_keys($changes)).')',
            'device' => $agent['device'],
            'browser' => $agent['browser'].' · '.$agent['os'],
            'ip_address' => $request->ip(),
        ]);
    }

    public static function passwordChanged($user, Request $request): void
    {
        $agent = (new self)->parseAgent($request);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'password_change',
            'description' => __('audit.activity.password_changed'),
            'device' => $agent['device'],
            'browser' => $agent['browser'].' · '.$agent['os'],
            'ip_address' => $request->ip(),
        ]);

        SecurityLog::create([
            'user_id' => $user->id,
            'event' => 'password_changed',
            'description' => __('audit.activity.account_password_changed'),
            'ip_address' => $request->ip(),
        ]);
    }

    public static function photoUploaded($user, Request $request): void
    {
        $agent = (new self)->parseAgent($request);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'photo_upload',
            'description' => __('audit.activity.photo_uploaded'),
            'device' => $agent['device'],
            'browser' => $agent['browser'].' · '.$agent['os'],
            'ip_address' => $request->ip(),
        ]);
    }

    public static function settingsUpdated($user, string $key, Request $request): void
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'settings_update',
            'description' => __('audit.activity.settings_updated', ['key' => $key]),
            'ip_address' => $request->ip(),
        ]);
    }

    public static function twoFactorEnabled($user, Request $request): void
    {
        SecurityLog::create([
            'user_id' => $user->id,
            'event' => '2fa_enabled',
            'description' => __('audit.activity.two_factor_enabled'),
            'ip_address' => $request->ip(),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => '2fa_enable',
            'description' => __('audit.activity.two_factor_enabled'),
            'ip_address' => $request->ip(),
        ]);
    }

    public static function twoFactorDisabled($user, Request $request): void
    {
        SecurityLog::create([
            'user_id' => $user->id,
            'event' => '2fa_disabled',
            'description' => __('audit.activity.two_factor_disabled'),
            'ip_address' => $request->ip(),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => '2fa_disable',
            'description' => __('audit.activity.two_factor_disabled'),
            'ip_address' => $request->ip(),
        ]);
    }
}
