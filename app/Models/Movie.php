<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_movie_id',
        'title',
        'content_type',
        'genre_primary',
        'genre_secondary',
        'release_year',
        'duration_minutes',
        'rating',
        'language',
        'country_of_origin',
        'imdb_rating',
        'production_budget',
        'box_office_revenue',
        'number_of_seasons',
        'number_of_episodes',
        'is_netflix_original',
        'added_to_platform',
        'content_warning',
    ];

    protected $casts = [
        'release_year' => 'integer',
        'duration_minutes' => 'integer',
        'imdb_rating' => 'decimal:1',
        'production_budget' => 'decimal:2',
        'box_office_revenue' => 'decimal:2',
        'number_of_seasons' => 'integer',
        'number_of_episodes' => 'integer',
        'is_netflix_original' => 'boolean',
        'added_to_platform' => 'date',
    ];

    /**
     * Get the reviews for the movie.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the users who have reviewed this movie.
     */
    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reviews', 'movie_id', 'user_id', 'movie_id', 'user_id')
                    ->withPivot('rating', 'review_text', 'review_date', 'device_type', 'is_verified_watch', 'helpful_votes', 'total_votes', 'sentiment', 'sentiment_score')
                    ->withTimestamps();
    }

    /**
     * Get the average rating for the movie.
     */
    public function averageRating()
    {
        return $this->reviews()->avg('rating');
    }

    /**
     * Get the total number of reviews for the movie.
     */
    public function reviewCount()
    {
        return $this->reviews()->count();
    }
}
