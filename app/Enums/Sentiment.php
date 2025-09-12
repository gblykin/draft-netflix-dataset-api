<?php

namespace App\Enums;

enum Sentiment: string
{
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';
    case NEUTRAL = 'neutral';

    /**
     * Get all sentiment values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all sentiment cases as an array.
     */
    public static function allCases(): array
    {
        return self::cases();
    }

    /**
     * Get a human-readable label for the sentiment.
     */
    public function label(): string
    {
        return match($this) {
            self::POSITIVE => 'Positive',
            self::NEGATIVE => 'Negative',
            self::NEUTRAL => 'Neutral',
        };
    }

    /**
     * Get the sentiment score range for this sentiment.
     */
    public function scoreRange(): array
    {
        return match($this) {
            self::POSITIVE => [0.6, 1.0],
            self::NEGATIVE => [0.0, 0.4],
            self::NEUTRAL => [0.4, 0.6],
        };
    }

    /**
     * Check if this sentiment is positive.
     */
    public function isPositive(): bool
    {
        return $this === self::POSITIVE;
    }

    /**
     * Check if this sentiment is negative.
     */
    public function isNegative(): bool
    {
        return $this === self::NEGATIVE;
    }

    /**
     * Check if this sentiment is neutral.
     */
    public function isNeutral(): bool
    {
        return $this === self::NEUTRAL;
    }
}
