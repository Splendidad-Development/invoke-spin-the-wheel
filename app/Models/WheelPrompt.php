<?php

namespace App\Models;

use App\Wheel;
use Database\Factories\WheelPromptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A question or challenge printed behind one numbered segment of a wheel.
 *
 * @property int $id
 * @property Wheel $wheel
 * @property int $position The segment number shown on the wheel, starting at 1.
 * @property string $body
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['wheel', 'position', 'body', 'is_active'])]
class WheelPrompt extends Model
{
    /** @use HasFactory<WheelPromptFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wheel' => Wheel::class,
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Active prompts for one wheel, in segment order.
     *
     * @param  Builder<WheelPrompt>  $query
     */
    public function scopeOnWheel(Builder $query, Wheel $wheel): void
    {
        $query->where('wheel', $wheel)->where('is_active', true)->orderBy('position');
    }

    /**
     * The dialog title, e.g. "Question 5".
     */
    public function title(): string
    {
        return "{$this->wheel->label()} {$this->position}";
    }
}
