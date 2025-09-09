<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_review_id' => $this->faker->unique()->uuid(),
            'user_id' => \App\Models\User::factory(), // Will be overridden in tests
            'movie_id' => \App\Models\Movie::factory(), // Will be overridden in tests
            'rating' => $this->faker->numberBetween(1, 5),
            'review_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'device_type' => $this->faker->randomElement(['Mobile', 'TV', 'Computer', 'Tablet']),
            'is_verified_watch' => $this->faker->boolean(70), // 70% chance of being verified
            'helpful_votes' => $this->faker->numberBetween(0, 100),
            'total_votes' => $this->faker->numberBetween(0, 200),
            'review_text' => $this->faker->optional(0.7)->paragraph(),
            'sentiment' => $this->faker->randomElement(['Positive', 'Negative', 'Neutral']),
            'sentiment_score' => $this->faker->randomFloat(4, -1.0, 1.0),
        ];
    }
}
