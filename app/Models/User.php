<?php

namespace App\Models;

use App\Enums\Device;
use App\Enums\Gender;
use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_user_id',
        'email',
        'first_name',
        'last_name',
        'age',
        'gender',
        'country',
        'state_province',
        'city',
        'subscription_plan',
        'subscription_start_date',
        'is_active',
        'monthly_spend',
        'primary_device',
        'household_size',
        'source_created_at',
    ];

    protected $casts = [
        'subscription_start_date' => 'date',
        'is_active' => 'boolean',
        'monthly_spend' => 'decimal:2',
        'household_size' => 'integer',
        'age' => 'integer',
        'gender' => Gender::class,
        'primary_device' => Device::class,
        'subscription_plan' => SubscriptionPlan::class,
        'source_created_at' => 'datetime',
    ];

    protected $attributes = [
        'monthly_spend' => 0.00,
        'household_size' => 1,
        'is_active' => true,
    ];

    /**
     * Get the reviews for the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the movies that the user has reviewed.
     */
    public function reviewedMovies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'reviews', 'user_id', 'movie_id')
                    ->withPivot('rating', 'review_text', 'review_date', 'device_type', 'is_verified_watch', 'helpful_votes', 'total_votes', 'sentiment', 'sentiment_score')
                    ->withTimestamps();
    }
}
