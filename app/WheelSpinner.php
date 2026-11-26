<?php

namespace App;

use App\Models\Game;
use App\Models\Spin;
use App\Models\WheelPrompt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Picks where a wheel lands. The server decides, the browser only animates to the answer.
 *
 * Each wheel works through its prompts in rounds: within a round every segment comes up once,
 * in random order. A wheel plays a fixed number of rounds per game (see Wheel::rounds());
 * after the last one it is finished until the game restarts.
 */
class WheelSpinner
{
    /**
     * Spin one wheel for a game and record the result.
     *
     * @throws ValidationException when the wheel has no active prompts or has played all its rounds.
     */
    public function spin(Game $game, Wheel $wheel): Spin
    {
        return DB::transaction(function () use ($game, $wheel): Spin {
            // Serialise spins per game so two quick taps can't both land on the same "unused" segment.
            Game::query()->whereKey($game->getKey())->lockForUpdate()->first();

            $prompts = $this->prompts($wheel);

            if ($prompts->isEmpty()) {
                throw ValidationException::withMessages([
                    'wheel' => "There are no {$wheel->plural()} on this wheel yet.",
                ]);
            }

            $progress = $this->progress($game, $wheel, $prompts);

            if ($progress['finished']) {
                throw ValidationException::withMessages([
                    'wheel' => "All {$wheel->plural()} have been played. Restart the game to play again.",
                ]);
            }

            /** @var WheelPrompt $prompt */
            $prompt = $prompts
                ->reject(fn (WheelPrompt $prompt): bool => in_array($prompt->id, $progress['usedIds'], true))
                ->random();

            $spin = $game->spins()->create([
                'wheel_prompt_id' => $prompt->id,
                'wheel' => $wheel,
            ]);

            return $spin->setRelation('prompt', $prompt);
        });
    }

    /**
     * Every wheel with its segments and where it is in its rounds.
     *
     * @return list<array{key: string, label: string, round: int, rounds: int, finished: bool, segments: list<array{position: int, used: bool}>}>
     */
    public function wheels(Game $game): array
    {
        return array_map(fn (Wheel $wheel): array => $this->wheel($game, $wheel), Wheel::cases());
    }

    /**
     * @return array{key: string, label: string, round: int, rounds: int, finished: bool, segments: list<array{position: int, used: bool}>}
     */
    public function wheel(Game $game, Wheel $wheel): array
    {
        $prompts = $this->prompts($wheel);
        $progress = $this->progress($game, $wheel, $prompts);

        return [
            'key' => $wheel->value,
            'label' => $wheel->label(),
            'round' => $progress['round'],
            'rounds' => $wheel->rounds(),
            'finished' => $progress['finished'],
            'segments' => array_values($prompts
                ->map(fn (WheelPrompt $prompt): array => [
                    'position' => $prompt->position,
                    'used' => $progress['finished'] || in_array($prompt->id, $progress['usedIds'], true),
                ])
                ->all()),
        ];
    }

    /**
     * @return Collection<int, WheelPrompt>
     */
    protected function prompts(Wheel $wheel): Collection
    {
        return WheelPrompt::query()->onWheel($wheel)->get();
    }

    /**
     * Replay the game's spins on this wheel to find the current round and what it has used.
     * A round completes once every active prompt has come up.
     *
     * @param  Collection<int, WheelPrompt>  $prompts
     * @return array{round: int, finished: bool, usedIds: list<int>}
     */
    protected function progress(Game $game, Wheel $wheel, Collection $prompts): array
    {
        $activeIds = $prompts->modelKeys();
        $completedRounds = 0;
        $used = [];

        $spunIds = $game->spins()
            ->where('wheel', $wheel)
            ->orderBy('id')
            ->pluck('wheel_prompt_id');

        foreach ($spunIds as $promptId) {
            if (! in_array($promptId, $activeIds, true)) {
                continue;
            }

            $used[$promptId] = true;

            if (count($used) >= count($activeIds)) {
                $completedRounds++;
                $used = [];
            }
        }

        $finished = $activeIds !== [] && $completedRounds >= $wheel->rounds();

        return [
            'round' => min($completedRounds + 1, $wheel->rounds()),
            'finished' => $finished,
            'usedIds' => array_keys($used),
        ];
    }
}
