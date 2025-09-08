<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'movie_id' => $this->faker->unique()->uuid(),
            'title' => $this->faker->sentence(3),
            'content_type' => $this->faker->randomElement(['Movie', 'TV Show']),
            'genre_primary' => $this->faker->randomElement(['Action', 'Comedy', 'Drama', 'Horror', 'Sci-Fi']),
            'genre_secondary' => $this->faker->randomElement(['Thriller', 'Romance', 'Adventure', 'Crime']),
            'release_year' => $this->faker->numberBetween(1990, 2024),
            'duration_minutes' => $this->faker->numberBetween(60, 180),
            'rating' => $this->faker->randomElement(['PG', 'PG-13', 'R', 'TV-MA']),
            'language' => $this->faker->randomElement(['English', 'Spanish', 'French', 'German']),
            'country_of_origin' => $this->faker->country(),
            'imdb_rating' => $this->faker->randomFloat(1, 1.0, 10.0),
            'production_budget' => $this->faker->numberBetween(1000000, 200000000),
            'box_office_revenue' => $this->faker->numberBetween(1000000, 1000000000),
            'number_of_seasons' => $this->faker->numberBetween(1, 10),
            'number_of_episodes' => $this->faker->numberBetween(1, 100),
            'is_netflix_original' => $this->faker->boolean(),
            'added_to_platform' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'content_warning' => $this->faker->optional()->sentence(),
        ];
    }
}
