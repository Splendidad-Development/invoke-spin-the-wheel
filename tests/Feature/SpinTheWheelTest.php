<?php

use App\Models\Game;
use App\Models\Player;
use App\Models\Spin;
use App\Models\WheelPrompt;
use App\Wheel;
use Database\Seeders\WheelPromptSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(WheelPromptSeeder::class);
});

/**
 * @return array<string, mixed>
 */
function spin(Game $game, Wheel $wheel): array
{
    return test()->postJson(route('spins.store', $game), ['wheel' => $wheel->value])
        ->assertCreated()
        ->json();
}

it('shows the intro screen', function () {
    $this->get(route('game.welcome'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('game/Welcome'));
});

it('opens a new game on "Get started" and resumes it afterwards', function () {
    $this->post(route('game.start'))->assertRedirect(route('game.show', $game = Game::query()->sole()));

    $this->post(route('game.start'))->assertRedirect(route('game.show', $game));

    expect(Game::query()->count())->toBe(1);
});

it('shows both wheels with fresh segments from the seeded content', function () {
    $game = Game::factory()->create();

    $this->get(route('game.show', $game))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('game/Board')
            ->where('game.id', $game->id)
            ->has('wheels', 2)
            ->where('wheels.0.key', 'question')
            ->where('wheels.0.label', 'Question')
            ->has('wheels.0.segments', 20)
            ->where('wheels.0.segments.0', ['position' => 1, 'used' => false])
            ->where('wheels.0.round', 1)
            ->where('wheels.0.rounds', 1)
            ->where('wheels.0.finished', false)
            ->where('wheels.1.key', 'challenge')
            ->has('wheels.1.segments', 10)
            ->where('wheels.1.round', 1)
            ->where('wheels.1.rounds', 2)
            ->where('players', [])
            ->where('results', []));
});

it('lands on a prompt and returns its question, options and answer', function () {
    $game = Game::factory()->create();

    $response = spin($game, Wheel::Question);

    $prompt = WheelPrompt::query()->where('wheel', Wheel::Question)->where('position', $response['spin']['position'])->sole();

    expect($response['spin'])->toMatchArray([
        'wheel' => 'question',
        'title' => "Question {$prompt->position}",
        'body' => $prompt->body,
        'answer' => $prompt->answer,
        'keyMessage' => $prompt->key_message,
    ])->and($response['spin']['options'])->toHaveCount(4)
        ->and($response['spin']['options'][0])->toBe(['letter' => 'A', 'text' => $prompt->options[0]])
        ->and(collect($response['wheel']['segments'])->firstWhere('position', $prompt->position)['used'])->toBeTrue()
        ->and(Spin::query()->sole()->wheel_prompt_id)->toBe($prompt->id);
});

it('returns a named challenge with no options', function () {
    $response = spin(Game::factory()->create(), Wheel::Challenge);

    $prompt = WheelPrompt::query()->where('wheel', Wheel::Challenge)->where('position', $response['spin']['position'])->sole();

    expect($response['spin'])->toMatchArray([
        'title' => "Challenge {$prompt->position}: {$prompt->name}",
        'body' => $prompt->body,
        'options' => [],
        'answer' => null,
        'keyMessage' => null,
    ]);
});

it('plays every question once, then the question wheel is finished', function () {
    $game = Game::factory()->create();

    $positions = collect(range(1, 20))->map(fn () => spin($game, Wheel::Question)['spin']['position']);

    expect($positions->sort()->values()->all())->toBe(range(1, 20));

    $this->postJson(route('spins.store', $game), ['wheel' => 'question'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['wheel' => 'All questions have been played. Restart the game to play again.']);

    $this->get(route('game.show', $game))
        ->assertInertia(fn (Assert $page) => $page
            ->where('wheels.0.finished', true)
            ->where('wheels.0.segments', fn ($segments) => collect($segments)->every(fn ($segment) => $segment['used'] === true)));
});

it('goes round the challenge wheel twice, each round covering all ten in random order', function () {
    $game = Game::factory()->create();

    $first = collect(range(1, 10))->map(fn () => spin($game, Wheel::Challenge)['spin']['position']);

    expect($first->sort()->values()->all())->toBe(range(1, 10));

    // Round two starts with every segment fresh again.
    $this->get(route('game.show', $game))
        ->assertInertia(fn (Assert $page) => $page
            ->where('wheels.1.round', 2)
            ->where('wheels.1.finished', false)
            ->where('wheels.1.segments', fn ($segments) => collect($segments)->every(fn ($segment) => $segment['used'] === false)));

    $second = collect(range(1, 10))->map(fn () => spin($game, Wheel::Challenge)['spin']['position']);

    expect($second->sort()->values()->all())->toBe(range(1, 10));

    $this->postJson(route('spins.store', $game), ['wheel' => 'challenge'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['wheel' => 'All challenges have been played. Restart the game to play again.']);

    $this->get(route('game.show', $game))
        ->assertInertia(fn (Assert $page) => $page->where('wheels.1.round', 2)->where('wheels.1.finished', true));

    // Restarting brings both wheels back.
    $this->post(route('game.restart', $game));

    expect(spin(Game::query()->latest('id')->first(), Wheel::Challenge)['spin']['position'])->toBeBetween(1, 10);
});

it('tracks each wheel separately', function () {
    $game = Game::factory()->create();

    spin($game, Wheel::Question);

    $this->get(route('game.show', $game))
        ->assertInertia(fn (Assert $page) => $page
            ->where('wheels.0.segments', fn ($segments) => collect($segments)->where('used', true)->count() === 1)
            ->where('wheels.1.segments', fn ($segments) => collect($segments)->where('used', true)->count() === 0));
});

it('skips prompts that have been switched off', function () {
    WheelPrompt::query()->where('wheel', Wheel::Question)->where('position', '>', 2)->update(['is_active' => false]);
    $game = Game::factory()->create();

    $positions = [spin($game, Wheel::Question)['spin']['position'], spin($game, Wheel::Question)['spin']['position']];

    sort($positions);
    expect($positions)->toBe([1, 2]);
});

it('refuses to spin a wheel with no prompts', function () {
    WheelPrompt::query()->where('wheel', Wheel::Challenge)->delete();

    $this->postJson(route('spins.store', Game::factory()->create()), ['wheel' => 'challenge'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('wheel');
});

it('rejects an unknown wheel', function () {
    $this->postJson(route('spins.store', Game::factory()->create()), ['wheel' => 'bonus'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('wheel');
});

it('saves the score sheet and drops empty rows', function () {
    $game = Game::factory()->create();

    $this->from(route('game.show', $game))
        ->put(route('players.update', $game), ['players' => [
            ['name' => '  Sara ', 'score' => '12'],
            ['name' => '', 'score' => ''],
            ['name' => 'Omar', 'score' => null],
        ]])
        ->assertRedirect(route('game.show', $game))
        ->assertSessionHasNoErrors();

    expect($game->players()->get(['name', 'score', 'position'])->toArray())->toBe([
        ['name' => 'Sara', 'score' => 12, 'position' => 0],
        ['name' => 'Omar', 'score' => 0, 'position' => 1],
    ]);
});

it('replaces the previous score sheet', function () {
    $game = Game::factory()->create();
    Player::factory()->for($game)->create(['name' => 'Old']);

    $this->put(route('players.update', $game), ['players' => [['name' => 'New', 'score' => 3]]]);

    expect($game->players()->pluck('name')->all())->toBe(['New']);
});

it('needs a name for every score', function () {
    $game = Game::factory()->create();

    $this->put(route('players.update', $game), ['players' => [['name' => '', 'score' => 5]]])
        ->assertSessionHasErrors(['players.0.name' => 'Add a name for this score.']);

    expect($game->players()->count())->toBe(0);
});

it('ranks the top ten, sharing ranks on equal scores', function () {
    $game = Game::factory()->create();
    $scores = [5, 9, 9, 2, 7, 1, 1, 0, 3, 4, 8, 6];

    foreach ($scores as $position => $score) {
        Player::factory()->for($game)->create(['name' => "P{$position}", 'score' => $score, 'position' => $position]);
    }

    $this->get(route('game.show', $game))
        ->assertInertia(fn (Assert $page) => $page
            ->has('players', 12)
            ->has('results', 10)
            ->where('results.0', ['rank' => 1, 'name' => 'P1', 'score' => 9])
            ->where('results.1', ['rank' => 1, 'name' => 'P2', 'score' => 9])
            ->where('results.2', ['rank' => 3, 'name' => 'P10', 'score' => 8])
            ->where('results.8', ['rank' => 9, 'name' => 'P3', 'score' => 2])
            // P5 and P6 tie on 1; the cut-off at ten keeps the one higher on the score sheet.
            ->where('results.9', ['rank' => 10, 'name' => 'P5', 'score' => 1]));
});

it('restarts into a fresh game and keeps the old one for reference', function () {
    $game = Game::factory()->create();
    Player::factory()->for($game)->create();
    spin($game, Wheel::Question);

    $this->post(route('game.restart', $game))
        ->assertRedirect(route('game.show', $next = Game::query()->latest('id')->first()));

    expect($game->fresh()->hasEnded())->toBeTrue()
        ->and($next->is($game))->toBeFalse()
        ->and($game->players()->count())->toBe(1);

    $this->get(route('game.show', $next))
        ->assertInertia(fn (Assert $page) => $page
            ->where('players', [])
            ->where('wheels.0.segments', fn ($segments) => collect($segments)->every(fn ($segment) => $segment['used'] === false)));
});

it('does not open two games when restart is pressed twice', function () {
    $game = Game::factory()->create();

    $this->post(route('game.restart', $game));
    $this->post(route('game.restart', $game));

    expect(Game::query()->inProgress()->count())->toBe(1);
});

it('sends an old game link to the game in progress', function () {
    $ended = Game::factory()->ended()->create();
    $current = Game::factory()->create();

    $this->get(route('game.show', $ended))->assertRedirect(route('game.show', $current));
});

it('blocks spins and scores on a game that has been restarted', function () {
    $ended = Game::factory()->ended()->create();

    $this->postJson(route('spins.store', $ended), ['wheel' => 'question'])->assertConflict();
    $this->put(route('players.update', $ended), ['players' => []])->assertConflict();
});

it('seeds all twenty questions as well-formed multiple-choice items', function () {
    $questions = WheelPrompt::query()->onWheel(Wheel::Question)->get();

    expect($questions)->toHaveCount(20)
        ->and($questions->pluck('position')->all())->toBe(range(1, 20));

    $questions->each(function (WheelPrompt $prompt) {
        expect($prompt->body)->not->toBeEmpty()
            ->and($prompt->name)->toBeNull()
            ->and($prompt->options)->toHaveCount(4)
            ->and(array_keys($prompt->lettered()))->toContain($prompt->answer);
    });
});

it('seeds ten named challenges with no options', function () {
    $challenges = WheelPrompt::query()->onWheel(Wheel::Challenge)->get();

    expect($challenges)->toHaveCount(10)
        ->and($challenges->first()->title())->toBe('Challenge 1: Sing It!');

    $challenges->each(function (WheelPrompt $prompt) {
        expect($prompt->name)->not->toBeEmpty()
            ->and($prompt->body)->not->toBeEmpty()
            ->and($prompt->options)->toBeNull()
            ->and($prompt->answer)->toBeNull();
    });
});

it('re-seeds in place: restores the text and switches off unknown segments', function () {
    WheelPrompt::query()->where('wheel', Wheel::Question)->where('position', 1)->update(['body' => 'Edited']);
    WheelPrompt::factory()->create(['wheel' => Wheel::Challenge, 'position' => 11]);

    $this->seed(WheelPromptSeeder::class);

    expect(WheelPrompt::query()->count())->toBe(31)
        ->and(WheelPrompt::query()->where('position', 1)->where('wheel', Wheel::Question)->value('body'))->toStartWith('According to SELECT-COMPARE')
        ->and(WheelPrompt::query()->where('wheel', Wheel::Challenge)->where('position', 11)->value('is_active'))->toBeFalse();
});

it('turns old multiple-choice rows into plain challenges when re-seeded', function () {
    WheelPrompt::query()->where('wheel', Wheel::Challenge)->where('position', 3)
        ->update(['options' => json_encode(['a', 'b', 'c', 'd']), 'answer' => 'B', 'key_message' => 'Old']);

    $this->seed(WheelPromptSeeder::class);

    $challenge = WheelPrompt::query()->where('wheel', Wheel::Challenge)->where('position', 3)->sole();

    expect($challenge->name)->toBe('Pass the Praise')
        ->and($challenge->options)->toBeNull()
        ->and($challenge->answer)->toBeNull()
        ->and($challenge->key_message)->toBeNull();
});
