<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One play-through on the event screen. "Restart" ends the current game and opens a fresh one,
 * so spins and scores from earlier sessions are kept for reference.
 *
 * @property int $id
 * @property Carbon|null $ended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['ended_at'])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ended_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Spin, $this>
     */
    public function spins(): HasMany
    {
        return $this->hasMany(Spin::class);
    }

    /**
     * @return HasMany<Player, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('position');
    }

    /**
     * @param  Builder<Game>  $query
     */
    public function scopeInProgress(Builder $query): void
    {
        $query->whereNull('ended_at');
    }

    public function hasEnded(): bool
    {
        return $this->ended_at !== null;
    }

    /**
     * The game that is currently being played, opening a new one if there is none.
     */
    public static function current(): self
    {
        return self::query()->inProgress()->latest('id')->first()
            ?? self::query()->create();
    }

    /**
     * End this game and open a fresh one.
     */
    public function restart(): self
    {
        $this->update(['ended_at' => now()]);

        return self::query()->create();
    }
}
