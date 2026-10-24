<?php

use App\Models\Admin;
use App\Models\PortalUser;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. The "web" guard is the admin
    | guard; portal users authenticate via the "portal" guard.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'admins'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Two guards are defined:
    |   - web    : admin authentication, backed by the `users` provider
    |              which points at the User model (admins table) on the
    |              admin DB connection.
    |   - portal : pemohon (asnaf) authentication, backed by the
    |              `portal_users` provider which points at the
    |              PortalUser model (users table) on the user DB
    |              connection.
    |
    | Each guard uses a distinct remember-me cookie name so an admin
    | remember-me cookie cannot be replayed against the portal guard
    | (and vice-versa).
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'admins',
            'cookie' => 'j_zakat_admin_remember',
        ],
        'portal' => [
            'driver' => 'session',
            'provider' => 'portal_users',
            'cookie' => 'j_zakat_portal_remember',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Two providers back the two guards. They resolve to different
    | Eloquent models on different database connections, so an admin
    | row and a pemohon row can never collide even if they share the
    | same email.
    |
    */

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
        'portal_users' => [
            'driver' => 'eloquent',
            'model' => PortalUser::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Two brokers, one per guard's user table. Each broker uses the
    | `password_reset_tokens` table on its respective database
    | connection.
    |
    */

    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        'portal_users' => [
            'provider' => 'portal_users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
