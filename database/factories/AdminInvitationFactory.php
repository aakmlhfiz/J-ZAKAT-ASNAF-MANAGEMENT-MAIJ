<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\AdminInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminInvitation>
 */
class AdminInvitationFactory extends Factory
{
    protected $model = AdminInvitation::class;

    public function definition(): array
    {
        return [
            'code' => AdminInvitation::generateCode(),
            'created_by' => Admin::factory(),
            'notes' => fake()->optional(0.6)->sentence(),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }

    public function used(): static
    {
        return $this->state(fn () => [
            'used_at' => now()->subDays(rand(1, 10)),
            'used_by' => Admin::factory(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
