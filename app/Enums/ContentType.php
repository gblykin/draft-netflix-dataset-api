<?php

namespace App\Enums;

enum ContentType: string
{
    case MOVIE = 'Movie';
    case TV_SERIES = 'TV Series';
    case DOCUMENTARY = 'Documentary';
    case STAND_UP_COMEDY = 'Stand-up Comedy';
    case LIMITED_SERIES = 'Limited Series';

    /**
     * Get all content type values as an array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all content type cases as an array.
     */
    public static function allCases(): array
    {
        return self::cases();
    }

    /**
     * Get a human-readable label for the content type.
     */
    public function label(): string
    {
        return match($this) {
            self::MOVIE => 'Movie',
            self::TV_SERIES => 'TV Series',
            self::DOCUMENTARY => 'Documentary',
            self::STAND_UP_COMEDY => 'Stand-up Comedy',
            self::LIMITED_SERIES => 'Limited Series',
        };
    }

    /**
     * Get the category of the content type.
     */
    public function category(): string
    {
        return match($this) {
            self::MOVIE => 'Film',
            self::TV_SERIES, self::LIMITED_SERIES => 'Television',
            self::DOCUMENTARY => 'Documentary',
            self::STAND_UP_COMEDY => 'Comedy',
        };
    }

    /**
     * Check if this content type is a series (TV Series or Limited Series).
     */
    public function isSeries(): bool
    {
        return in_array($this, [self::TV_SERIES, self::LIMITED_SERIES]);
    }

    /**
     * Check if this content type is episodic content.
     */
    public function isEpisodic(): bool
    {
        return $this->isSeries();
    }

    /**
     * Check if this content type is a movie.
     */
    public function isMovie(): bool
    {
        return $this === self::MOVIE;
    }

    /**
     * Check if this content type is documentary content.
     */
    public function isDocumentary(): bool
    {
        return $this === self::DOCUMENTARY;
    }
}
