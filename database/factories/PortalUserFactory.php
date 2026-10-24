<?php

namespace Database\Factories;

use App\Models\PortalUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<PortalUser>
 */
class PortalUserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'ic_no' => fake()->numerify('############'),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => fake()->numerify('01#########'),
            'address' => fake()->streetAddress(),
            'district' => 'Johor Bahru',
            'state' => 'Johor',
            'postcode' => '80000',
            'password' => static::$password ??= Hash::make('password'),
            'status' => 'active',
            'dark_mode' => 'system',
            'language' => 'ms',
            'notification_email' => true,
            'notification_push' => true,
            'notification_sms' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
