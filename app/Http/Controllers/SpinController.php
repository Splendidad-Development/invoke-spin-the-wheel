<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpinRequest;
use App\Models\Game;
use App\Wheel;
use App\WheelSpinner;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SpinController extends Controller
{
    /**
     * Spin a wheel. Returns where it landed so the browser can animate to it,
     * plus the wheel's updated segments.
     */
    public function store(StoreSpinRequest $request, Game $game, WheelSpinner $spinner): JsonResponse
    {
        abort_if($game->hasEnded(), Response::HTTP_CONFLICT, 'This game has been restarted. Reload the page to keep playing.');

        $wheel = $request->enum('wheel', Wheel::class);
        $spin = $spinner->spin($game, $wheel);

        return response()->json([
            'spin' => [
                'id' => $spin->id,
                'wheel' => $wheel->value,
                'position' => $spin->prompt->position,
                'title' => $spin->prompt->title(),
                'body' => $spin->prompt->body,
                'options' => collect($spin->prompt->lettered())
                    ->map(fn (string $text, string $letter): array => ['letter' => $letter, 'text' => $text])
                    ->values()
                    ->all(),
                'answer' => $spin->prompt->answer,
                'keyMessage' => $spin->prompt->key_message,
            ],
            'wheel' => $spinner->wheel($game, $wheel),
        ], Response::HTTP_CREATED);
    }
}
