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
}
