<?php

namespace App;

/**
 * The two wheels on the board. The left one hands out a question, the right one a challenge.
 */
enum Wheel: string
{
    case Question = 'question';
    case Challenge = 'challenge';

    /**
     * The word shown on the wheel's card and in the prompt dialog title, e.g. "Question 5".
     */
    public function label(): string
    {
        return match ($this) {
            self::Question => 'Question',
            self::Challenge => 'Challenge',
        };
    }

    /**
     * How many times a game goes round this wheel. Each round lands on every segment once,
     * in random order; after the last round the wheel is done until the game restarts.
     * Twenty questions once and ten challenges twice gives twenty turns on each wheel.
     */
    public function rounds(): int
    {
        return match ($this) {
            self::Question => 1,
            self::Challenge => 2,
        };
    }

    /**
     * Plural used in messages, e.g. "All challenges have been played".
     */
    public function plural(): string
    {
        return match ($this) {
            self::Question => 'questions',
            self::Challenge => 'challenges',
        };
    }
}
