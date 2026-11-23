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
 * Each wheel works through its prompts in rounds: a segment can't come up again until every
 * other segment on that wheel has had its turn, then the wheel starts a fresh round.
 */
class WheelSpinner
{
    /**
     * Spin one wheel for a game and record the result.
     *
     * @throws ValidationException when the wheel has no active prompts.
     */
    public function spin(Game $game, Wheel $wheel): Spin
    {
        return DB::transaction(function () use ($game, $wheel): Spin {
            // Serialise spins per game so two quick taps can't both land on the same "unused" segment.
            Game::query()->whereKey($game->getKey())->lockForUpdate()->first();

            $prompts = $this->prompts($wheel);

            if ($prompts->isEmpty()) {
                throw ValidationException::withMessages([
                    'wheel' => "There are no {$wheel->label()} prompts on this wheel yet.",
                ]);
            }

            $usedIds = $this->usedPromptIds($game, $wheel, $prompts);
            $candidates = $prompts->reject(fn (WheelPrompt $prompt): bool => in_array($prompt->id, $usedIds, true));

            /** @var WheelPrompt $prompt */
            $prompt = ($candidates->isEmpty() ? $prompts : $candidates)->random();

            $spin = $game->spins()->create([
                'wheel_prompt_id' => $prompt->id,
                'wheel' => $wheel,
            ]);

            return $spin->setRelation('prompt', $prompt);
        });
    }

    /**
     * Every wheel with its segments and whether each one has come up this round.
     *
     * @return list<array{key: string, label: string, segments: list<array{position: int, used: bool}>}>
     */
    public function wheels(Game $game): array
    {
        return array_map(fn (Wheel $wheel): array => $this->wheel($game, $wheel), Wheel::cases());
    }

    /**
     * @return array{key: string, label: string, segments: list<array{position: int, used: bool}>}
     */
    public function wheel(Game $game, Wheel $wheel): array
    {
        $prompts = $this->prompts($wheel);
        $usedIds = $this->usedPromptIds($game, $wheel, $prompts);

        return [
            'key' => $wheel->value,
            'label' => $wheel->label(),
            'segments' => array_values($prompts
                ->map(fn (WheelPrompt $prompt): array => [
                    'position' => $prompt->position,
                    'used' => in_array($prompt->id, $usedIds, true),
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
     * Prompts already landed on in the wheel's current round.
     *
     * Replays the game's spins in order; once every active prompt has come up, the round resets.
     *
     * @param  Collection<int, WheelPrompt>  $prompts
     * @return list<int>
     */
    protected function usedPromptIds(Game $game, Wheel $wheel, Collection $prompts): array
    {
        $activeIds = $prompts->modelKeys();
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
                $used = [];
            }
        }

        return array_keys($used);
    }
}
