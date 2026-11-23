<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePlayersRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PlayerController extends Controller
{
    /**
     * Save the whole score sheet. Rows without a name are dropped.
     */
    public function update(UpdatePlayersRequest $request, Game $game): RedirectResponse
    {
        abort_if($game->hasEnded(), Response::HTTP_CONFLICT, 'This game has been restarted. Reload the page to keep playing.');

        DB::transaction(function () use ($request, $game): void {
            $game->players()->delete();

            $request->filledRows()->values()->each(
                fn (array $row, int $position) => $game->players()->create([
                    'name' => $row['name'],
                    'score' => $row['score'] ?? 0,
                    'position' => $position,
                ]),
            );
        });

        return back();
    }
}
