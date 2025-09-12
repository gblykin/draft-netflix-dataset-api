<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Enums\Device;
use App\Enums\Gender;
use App\Enums\Sentiment;
use App\Enums\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Movie;
use App\Models\User;
use App\Models\Review;

class MovieApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    private function createTestData()
    {
        // Create test movies
        Movie::create([
            'external_movie_id' => 'test_movie_1',
            'title' => 'Test Movie 1',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Action',
            'genre_secondary' => 'Thriller',
            'release_year' => 2023,
            'duration_minutes' => 120,
            'rating' => 'PG-13',
            'language' => 'English',
            'country_of_origin' => 'USA',
            'imdb_rating' => 7.5,
            'production_budget' => 50000000,
            'box_office_revenue' => 100000000,
            'is_netflix_original' => false,
            'added_to_platform' => '2023-01-01',
        ]);

        Movie::create([
            'external_movie_id' => 'test_movie_2',
            'title' => 'Test Movie 2',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Comedy',
            'genre_secondary' => 'Romance',
            'release_year' => 2023,
            'duration_minutes' => 90,
            'rating' => 'R',
            'language' => 'English',
            'country_of_origin' => 'Canada',
            'imdb_rating' => 8.0,
            'production_budget' => 30000000,
            'box_office_revenue' => 80000000,
            'is_netflix_original' => true,
            'added_to_platform' => '2023-02-01',
        ]);
    }

    public function test_can_get_movies_list()
    {
        $response = $this->getJson('/api/movies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'title', 'genre_primary', 'release_year']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total']
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_movies_by_genre()
    {
        $response = $this->getJson('/api/movies?genre=Action');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.genre_primary', 'Action');
    }

    public function test_can_get_single_movie()
    {
        $movie = Movie::first();
        
        $response = $this->getJson("/api/movies/{$movie->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'genre_primary', 'release_year', 'imdb_rating']
            ])
            ->assertJsonPath('data.id', $movie->id)
            ->assertJsonPath('data.title', $movie->title);
    }

    public function test_movie_not_found()
    {
        $response = $this->getJson('/api/movies/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\Movie] 999999']);
    }
}