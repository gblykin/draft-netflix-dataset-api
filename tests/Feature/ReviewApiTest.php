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

class ReviewApiTest extends TestCase
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
        // Create test user
        $user = User::create([
            'external_user_id' => 'test_user_1',
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => Gender::MALE,
            'date_of_birth' => '1990-01-01',
            'country' => 'USA',
            'city' => 'New York',
            'subscription_plan' => SubscriptionPlan::PREMIUM,
            'subscription_start_date' => '2023-01-01',
            'primary_device' => Device::MOBILE,
            'source_created_at' => '2023-01-01 10:00:00',
        ]);

        // Create test movie
        $movie = Movie::create([
            'external_movie_id' => 'test_movie_1',
            'title' => 'Test Movie 1',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ]);

        // Create test reviews
        Review::create([
            'external_review_id' => 'test_review_1',
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'rating' => 5,
            'review_text' => 'Great movie!',
            'review_date' => '2023-01-15',
            'device_type' => Device::MOBILE,
            'is_verified_watch' => true,
            'helpful_votes' => 10,
            'total_votes' => 12,
            'sentiment' => Sentiment::POSITIVE,
            'sentiment_score' => 0.8,
        ]);

        Review::create([
            'external_review_id' => 'test_review_2',
            'user_id' => $user->id,
            'movie_id' => $movie->id,
            'rating' => 3,
            'review_text' => 'Average movie',
            'review_date' => '2023-01-16',
            'device_type' => Device::DESKTOP,
            'is_verified_watch' => false,
            'helpful_votes' => 5,
            'total_votes' => 8,
            'sentiment' => Sentiment::NEUTRAL,
            'sentiment_score' => 0.2,
        ]);
    }

    public function test_can_get_reviews_list()
    {
        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'rating', 'review_text', 'user', 'movie']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total']
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_get_single_review()
    {
        $review = Review::first();
        
        $response = $this->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'rating', 'review_text', 'user', 'movie']
            ])
            ->assertJsonPath('data.id', $review->id)
            ->assertJsonPath('data.rating', $review->rating);
    }

    public function test_review_not_found()
    {
        $response = $this->getJson('/api/reviews/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\Review] 999999']);
    }
}