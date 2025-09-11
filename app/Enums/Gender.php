<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'Male';
    case FEMALE = 'Female';
    case PREFER_NOT_TO_SAY = 'Prefer not to say';
    case OTHER = 'Other';

    /**
     * Get all gender values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all gender cases as an array.
     */
    public static function allCases(): array
    {
        return self::cases();
    }

    /**
     * Get a human-readable label for the gender.
     */
    public function label(): string
    {
        return match($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::PREFER_NOT_TO_SAY => 'Prefer not to say',
            self::OTHER => 'Other',
        };
    }
}
