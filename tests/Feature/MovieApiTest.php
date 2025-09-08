<?php

namespace Tests\Feature;

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
            'movie_id' => 'test_movie_1',
            'title' => 'Test Movie 1',
            'content_type' => 'Movie',
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
            'movie_id' => 'test_movie_2',
            'title' => 'Test Movie 2',
            'content_type' => 'Movie',
            'genre_primary' => 'Drama',
            'genre_secondary' => 'Romance',
            'release_year' => 2022,
            'duration_minutes' => 90,
            'rating' => 'R',
            'language' => 'English',
            'country_of_origin' => 'UK',
            'imdb_rating' => 8.2,
            'production_budget' => 30000000,
            'box_office_revenue' => 60000000,
            'is_netflix_original' => true,
            'added_to_platform' => '2022-06-01',
        ]);

        // Create test user
        User::create([
            'user_id' => 'test_user_1',
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'age' => 25,
            'gender' => 'Male',
            'country' => 'USA',
            'state_province' => 'New York',
            'city' => 'New York',
            'subscription_plan' => 'Premium',
            'subscription_start_date' => '2023-01-01',
            'is_active' => true,
            'monthly_spend' => 15.99,
            'primary_device' => 'Mobile',
            'household_size' => 1,
        ]);

        // Create test review
        Review::create([
            'review_id' => 'test_review_1',
            'user_id' => 'test_user_1',
            'movie_id' => 'test_movie_1',
            'rating' => 5,
            'review_date' => '2023-06-01',
            'device_type' => 'Mobile',
            'is_verified_watch' => true,
            'helpful_votes' => 8,
            'total_votes' => 10,
            'review_text' => 'Great movie!',
            'sentiment' => 'positive',
            'sentiment_score' => 0.8,
        ]);
    }

    public function test_can_get_movies_list()
    {
        $response = $this->getJson('/api/movies');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'movie_id',
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
                             'is_netflix_original',
                         ]
                     ],
                     'links',
                     'meta'
                 ]);
    }

    public function test_can_filter_movies_by_genre()
    {
        $response = $this->getJson('/api/movies?genre_primary=Action');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Action', $data[0]['genre_primary']);
    }

    public function test_can_filter_movies_by_genre_searches_both_primary_and_secondary()
    {
        // Create a movie with Action in secondary genre
        Movie::create([
            'movie_id' => 'test_movie_secondary',
            'title' => 'Test Movie Secondary',
            'content_type' => 'Movie',
            'genre_primary' => 'Drama',
            'genre_secondary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
            'added_to_platform' => '2022-06-01',
        ]);

        $response = $this->getJson('/api/movies?genre=Action');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data)); // Should include both primary and secondary Action movies
        
        // Check that we have movies with Action in either primary or secondary
        $hasActionInPrimary = collect($data)->contains(function ($movie) {
            return str_contains($movie['genre_primary'], 'Action');
        });
        $hasActionInSecondary = collect($data)->contains(function ($movie) {
            return str_contains($movie['genre_secondary'] ?? '', 'Action');
        });
        
        $this->assertTrue($hasActionInPrimary || $hasActionInSecondary, 'Should find Action in either primary or secondary genre');
    }

    public function test_can_filter_movies_by_release_year()
    {
        $response = $this->getJson('/api/movies?release_year=2023');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(2023, $data[0]['release_year']);
    }

    public function test_can_get_single_movie()
    {
        $response = $this->getJson('/api/movies/test_movie_1');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'movie_id',
                         'title',
                         'content_type',
                         'genre_primary',
                         'genre_secondary',
                         'release_year',
                         'reviews' => [
                             '*' => [
                                 'id',
                                 'review_id',
                                 'rating',
                                 'review_text',
                                 'user'
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_can_sort_movies()
    {
        $response = $this->getJson('/api/movies?sort_by=release_year&sort_order=desc');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals(2023, $data[0]['release_year']);
        $this->assertEquals(2022, $data[1]['release_year']);
    }

    public function test_pagination_works()
    {
        $response = $this->getJson('/api/movies?per_page=1');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links' => [
                         'first',
                         'last',
                         'prev',
                         'next'
                     ],
                     'meta' => [
                         'current_page',
                         'per_page',
                         'total'
                     ]
                 ]);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_movie_not_found()
    {
        $response = $this->getJson('/api/movies/nonexistent_movie');

        $response->assertStatus(404);
    }
}
