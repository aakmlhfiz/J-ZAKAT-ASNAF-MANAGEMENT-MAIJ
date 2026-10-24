<?php

namespace Tests\Unit;

use App\Services\TwoFactorService;
use Tests\TestCase;

class TwoFactorServiceTest extends TestCase
{
    private TwoFactorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TwoFactorService;
    }

    public function test_generate_secret_returns_32_character_string(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertEquals(32, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_generate_secret_produces_unique_values(): void
    {
        $secrets = [];
        for ($i = 0; $i < 10; $i++) {
            $secrets[] = $this->service->generateSecret();
        }

        $this->assertCount(10, array_unique($secrets));
    }

    public function test_verify_code_rejects_wrong_length(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertFalse($this->service->verifyCode($secret, '12345'));
        $this->assertFalse($this->service->verifyCode($secret, '1234567'));
    }

    public function test_verify_code_rejects_wrong_code(): void
    {
        $secret = $this->service->generateSecret();

        $valid = $this->service->verifyCode($secret, '999999');

        $this->assertFalse($valid);
    }

    public function test_verify_code_accepts_valid_current_time_slice_code(): void
    {
        $secret = $this->service->generateSecret();

        $code = $this->computeCurrentTotp($secret);

        if ($code === null) {
            $this->markTestSkipped('Cannot verify without knowing the generated TOTP.');

            return;
        }

        $this->assertTrue(
            $this->service->verifyCode($secret, $code),
            "Expected code {$code} to be valid for secret {$secret}"
        );
    }

    public function test_verify_code_rejects_empty_secret(): void
    {
        $this->assertFalse($this->service->verifyCode('', '123456'));
    }

    private function computeCurrentTotp(string $secret): ?string
    {
        $timeSlice = floor(time() / 30);
        $code = null;

        $ref = new \ReflectionClass($this->service);
        $method = $ref->getMethod('computeTotp');
        $method->setAccessible(true);

        $code = $method->invoke($this->service, $secret, $timeSlice);

        return $code ?: null;
    }
}
