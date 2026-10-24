<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FirebaseService
{
    private const SIGN_UP_URL = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp';

    public function __construct(
        private readonly string $apiKey,
    ) {}

    public function createUser(string $email, string $password, string $displayName): string
    {
        $response = Http::post(self::SIGN_UP_URL.'?key='.$this->apiKey, [
            'email' => $email,
            'password' => $password,
            'displayName' => $displayName,
            'returnSecureToken' => true,
        ]);

        $body = $response->json();

        if ($response->successful() && isset($body['localId'])) {
            Log::info('Firebase user created', ['uid' => $body['localId'], 'email' => $email]);

            return $body['localId'];
        }

        if (isset($body['error']['message'])) {
            if ($body['error']['message'] === 'EMAIL_EXISTS') {
                Log::warning('Firebase user already exists', ['email' => $email]);
                throw new RuntimeException('Email sudah wujud dalam Firebase.');
            }

            if (str_contains($body['error']['message'], 'API_KEY')) {
                Log::error('Firebase API key restriction', ['error' => $body['error']['message']]);
                throw new RuntimeException('Konfigurasi API key Firebase tidak membenarkan panggilan server. Sila semak sekatan API key di Google Cloud Console.');
            }

            Log::error('Firebase signUp failed', ['error' => $body['error']]);
            throw new RuntimeException('Firebase error: '.$body['error']['message']);
        }

        Log::error('Firebase signUp unexpected response', ['status' => $response->status(), 'body' => $body]);
        throw new RuntimeException('Gagal mencipta akaun Firebase.');
    }
}
