<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\WheelSpinner;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    /**
     * How many players the results dialog lists.
     */
    public const RESULTS_LIMIT = 10;

    public function welcome(): Response
    {
        return Inertia::render('game/Welcome');
    }

    /**
     * "Get started": carry on with the game in progress, or open a new one.
     */
    public function start(): RedirectResponse
    {
        return to_route('game.show', Game::current());
    }

    public function show(Game $game, WheelSpinner $spinner): Response|RedirectResponse
    {
        if ($game->hasEnded()) {
            return to_route('game.show', Game::current());
        }

        $players = $game->players()->get();

        return Inertia::render('game/Board', [
            'game' => ['id' => $game->id],
            'wheels' => $spinner->wheels($game),
            'players' => $players
                ->map(fn (Player $player): array => $player->only('name', 'score'))
                ->all(),
            'results' => $this->results($game),
        ]);
    }

    /**
     * End the current game (spins and scores are kept) and open a fresh one.
     */
    public function restart(Game $game): RedirectResponse
    {
        $next = $game->hasEnded() ? Game::current() : $game->restart();

        return to_route('game.show', $next);
    }

    /**
     * Top players, best first. Equal scores share a rank (1, 2, 2, 4).
     *
     * @return list<array{rank: int, name: string, score: int}>
     */
    protected function results(Game $game): array
    {
        $rows = [];
        $previousScore = null;
        $rank = 0;

        $ranked = Player::query()
            ->whereBelongsTo($game)
            ->ranked()
            ->limit(self::RESULTS_LIMIT)
            ->get();

        foreach ($ranked as $index => $player) {
            if ($player->score !== $previousScore) {
                $rank = $index + 1;
                $previousScore = $player->score;
            }

            $rows[] = ['rank' => $rank, 'name' => $player->name, 'score' => $player->score];
        }

        return $rows;
    }
}
