<?php

namespace App\Models;

use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A row on the host's score sheet.
 *
 * @property int $id
 * @property int $game_id
 * @property string $name
 * @property int $score
 * @property int $position Row order on the score sheet, starting at 0.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['game_id', 'name', 'score', 'position'])]
class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'position' => 'integer',
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
     * Highest score first; ties keep score sheet order.
     *
     * @param  Builder<Player>  $query
     */
    public function scopeRanked(Builder $query): void
    {
        $query->reorder()->orderByDesc('score')->orderBy('position')->orderBy('id');
    }
}
