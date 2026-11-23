<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class UpdatePlayersRequest extends FormRequest
{
    /**
     * The most rows the score sheet accepts.
     */
    public const MAX_PLAYERS = 50;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'players' => ['present', 'array', 'max:'.self::MAX_PLAYERS],
            'players.*' => ['array:name,score'],
            'players.*.name' => ['nullable', 'string', 'max:40', 'required_with:players.*.score'],
            'players.*.score' => ['nullable', 'integer', 'min:-9999', 'max:99999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'players.*.name.required_with' => 'Add a name for this score.',
            'players.*.name.max' => 'Keep names under 40 characters.',
            'players.*.score.integer' => 'Scores must be whole numbers.',
            'players.max' => 'The score sheet holds up to '.self::MAX_PLAYERS.' players.',
        ];
    }

    /**
     * The rows that have a player name, in score sheet order.
     *
     * @return Collection<int, array{name: string, score: int|null}>
     */
    public function filledRows(): Collection
    {
        /** @var list<array{name?: string|null, score?: int|string|null}> $rows */
        $rows = $this->validated('players');

        return collect($rows)
            ->filter(fn (array $row): bool => filled($row['name'] ?? null))
            ->map(fn (array $row): array => [
                'name' => (string) $row['name'],
                'score' => isset($row['score']) ? (int) $row['score'] : null,
            ]);
    }
}
