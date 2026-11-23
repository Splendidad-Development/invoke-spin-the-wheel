<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ended_at' => null,
        ];
    }

    /**
     * A game that was restarted.
     */
    public function ended(): static
    {
        return $this->state(fn (): array => ['ended_at' => now()]);
    }
}
