<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'movie_id',
        'rating',
        'review_date',
        'device_type',
        'is_verified_watch',
        'helpful_votes',
        'total_votes',
        'review_text',
        'sentiment',
        'sentiment_score',
    ];

    protected $casts = [
        'review_date' => 'date',
        'rating' => 'integer',
        'is_verified_watch' => 'boolean',
        'helpful_votes' => 'integer',
        'total_votes' => 'integer',
        'sentiment_score' => 'decimal:4',
    ];

    /**
     * Get the user that owns the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the movie that the review belongs to.
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'movie_id');
    }
}
