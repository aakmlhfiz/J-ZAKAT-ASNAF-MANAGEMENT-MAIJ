<?php

namespace App\Services\Concerns;

use Illuminate\Http\Request;

trait ParsesUserAgent
{
    protected function parseAgent(?Request $request): array
    {
        $ua = $request?->userAgent() ?? '';

        $browser = 'Tidak diketahui';
        $os = 'Tidak diketahui';
        $device = 'Desktop';

        if (preg_match('/(Chrome|Chromium)\/(\d+)/i', $ua, $m)) {
            $browser = 'Chrome '.$m[2];
        } elseif (preg_match('/Firefox\/(\d+)/i', $ua, $m)) {
            $browser = 'Firefox '.$m[1];
        } elseif (preg_match('/Edg\/(\d+)/i', $ua, $m)) {
            $browser = 'Edge '.$m[1];
        } elseif (preg_match('/Version\/(\d+).*Safari/i', $ua, $m) && ! preg_match('/Chrome/i', $ua)) {
            $browser = 'Safari '.$m[1];
        }

        if (preg_match('/Windows NT (\d+\.\d+)/i', $ua, $m)) {
            $os = 'Windows '.match ((float) $m[1]) {
                10.0 => '10/11', 6.3 => '8.1', 6.2 => '8', 6.1 => '7', default => $m[1],
            };
        } elseif (preg_match('/Mac OS X (\d+[._]\d+)/i', $ua, $m)) {
            $os = 'macOS '.str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Android (\d+)/i', $ua, $m)) {
            $os = 'Android '.$m[1];
        } elseif (preg_match('/iPhone OS (\d+)/i', $ua, $m)) {
            $os = 'iOS '.$m[1];
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        if (preg_match('/Mobi|Android/i', $ua) && ! preg_match('/iPad/i', $ua)) {
            $device = 'Telefon';
        } elseif (preg_match('/iPad|Tablet/i', $ua)) {
            $device = 'Tablet';
        }

        return compact('browser', 'os', 'device');
    }

    protected function resolveLocation(?Request $request): ?string
    {
        return null;
    }
}
