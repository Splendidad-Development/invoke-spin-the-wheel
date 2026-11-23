<?php

namespace App\Models;

use App\Wheel;
use Database\Factories\SpinFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One turn of a wheel and the prompt it landed on.
 *
 * @property int $id
 * @property int $game_id
 * @property int $wheel_prompt_id
 * @property Wheel $wheel
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['game_id', 'wheel_prompt_id', 'wheel'])]
class Spin extends Model
{
    /** @use HasFactory<SpinFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wheel' => Wheel::class,
        ];
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<WheelPrompt, $this>
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(WheelPrompt::class, 'wheel_prompt_id');
    }
}
