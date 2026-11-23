<?php

namespace Database\Factories;

use App\Models\WheelPrompt;
use App\Wheel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WheelPrompt>
 */
class WheelPromptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wheel' => Wheel::Question,
            'position' => fake()->unique()->numberBetween(1, 200),
            'body' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
