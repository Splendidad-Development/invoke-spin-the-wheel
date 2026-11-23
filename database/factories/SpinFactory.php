<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Spin;
use App\Models\WheelPrompt;
use App\Wheel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Spin>
 */
class SpinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'wheel_prompt_id' => WheelPrompt::factory(),
            'wheel' => fn (array $attributes): Wheel => WheelPrompt::query()->whereKey($attributes['wheel_prompt_id'])->sole()->wheel,
        ];
    }
}
