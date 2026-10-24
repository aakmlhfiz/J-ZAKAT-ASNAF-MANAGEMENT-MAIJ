<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Http\Request;

use function floor;
use function hash_hmac;
use function str_pad;

class TwoFactorService
{
    public function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $secret;
    }

    public function getQrCodeUrl(Admin $user): string
    {
        if (! $user->two_factor_secret) {
            return '';
        }

        $issuer = rawurlencode('J-ZAKAT');
        $account = rawurlencode($user->email);

        return "otpauth://totp/{$issuer}:{$account}?secret={$user->two_factor_secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    public function verifyCode(string $secret, string $code): bool
    {
        if (strlen($code) !== 6) {
            return false;
        }

        $timeSlice = floor(time() / 30);

        for ($i = -1; $i <= 1; $i++) {
            if ($this->computeTotp($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    private function computeTotp(string $secret, int $timeSlice): string
    {
        $secret = $this->base32Decode($secret);
        $time = pack('J', $timeSlice);
        $time = str_pad($time, 8, "\0", STR_PAD_LEFT);

        $hash = hash_hmac('sha1', $time, $secret, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = (ord($hash[$offset]) & 0x7F) << 24
            | (ord($hash[$offset + 1]) & 0xFF) << 16
            | (ord($hash[$offset + 2]) & 0xFF) << 8
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % 1000000;

        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper(rtrim($secret, '='));

        $binary = '';
        foreach (str_split($secret) as $char) {
            $val = strpos($alphabet, $char);
            if ($val === false) {
                continue;
            }
            $binary .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) < 8) {
                break;
            }
            $result .= chr(bindec($byte));
        }

        return $result;
    }

    public function enable(Admin $user, string $code, Request $request): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        $valid = $this->verifyCode($user->two_factor_secret, $code);

        if ($valid) {
            $user->two_factor_confirmed_at = now();
            $user->save();

            ActivityLogger::twoFactorEnabled($user, $request);
        }

        return $valid;
    }

    public function disable(Admin $user, Request $request): void
    {
        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        ActivityLogger::twoFactorDisabled($user, $request);
    }
}
