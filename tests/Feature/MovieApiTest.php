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
            'external_user_id' => 'test_user_1',
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'age' => 25,
            'gender' => Gender::MALE,
            'country' => 'USA',
            'state_province' => 'New York',
            'city' => 'New York',
            'subscription_plan' => SubscriptionPlan::PREMIUM,
            'subscription_start_date' => '2023-01-01',
            'is_active' => true,
            'monthly_spend' => 15.99,
            'primary_device' => Device::MOBILE,
            'household_size' => 1,
            'source_created_at' => '2023-01-01 10:00:00',
        ]);

        // Create test review
        $movie = Movie::where('external_movie_id', 'test_movie_1')->first();
        $user = User::where('external_user_id', 'test_user_1')->first();
        
        $review = Review::create([
            'external_review_id' => 'test_review_1',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 5,
            'review_date' => '2023-06-01',
            'device_type' => Device::MOBILE,
            'is_verified_watch' => true,
            'helpful_votes' => 8,
            'total_votes' => 10,
            'review_text' => 'Great movie!',
            'sentiment' => Sentiment::POSITIVE,
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
            'external_movie_id' => 'test_movie_secondary',
            'title' => 'Test Movie Secondary',
            'content_type' => ContentType::MOVIE,
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
        $movie = Movie::where('external_movie_id', 'test_movie_1')->first();
        $response = $this->getJson('/api/movies/' . $movie->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
            'data' => [
                'id',
                'external_movie_id',
                'title',
                'content_type',
                'genre_primary',
                'genre_secondary',
                'release_year',
                'reviews' => [
                    '*' => [
                        'id',
                        'external_review_id',
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
        $response = $this->getJson('/api/movies/' . PHP_INT_MAX);

        $response->assertStatus(404);
    }

    public function test_can_filter_movies_by_boolean_parameters()
    {
        // Test is_netflix_original=true
        $response = $this->getJson('/api/movies?is_netflix_original=true');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_netflix_original']);

        // Test is_netflix_original=false
        $response = $this->getJson('/api/movies?is_netflix_original=false');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertFalse($data[0]['is_netflix_original']);
    }

    public function test_boolean_parameter_validation_works()
    {
        // Test invalid boolean value
        $response = $this->getJson('/api/movies?is_netflix_original=invalid');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_netflix_original']);
    }

    public function test_can_combine_multiple_filters()
    {
        $response = $this->getJson('/api/movies?content_type=' . ContentType::MOVIE->value . '&is_netflix_original=true');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Movie', $data[0]['content_type']);
        $this->assertTrue($data[0]['is_netflix_original']);
    }
}
