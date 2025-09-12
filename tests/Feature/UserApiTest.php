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

class UserApiTest extends TestCase
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
        // Create test users
        $user1 = User::create([
            'external_user_id' => 'test_user_1',
            'email' => 'user1@example.com',
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

        $user2 = User::create([
            'external_user_id' => 'test_user_2',
            'email' => 'user2@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'gender' => Gender::FEMALE,
            'date_of_birth' => '1992-05-15',
            'country' => 'Canada',
            'city' => 'Toronto',
            'subscription_plan' => SubscriptionPlan::BASIC,
            'subscription_start_date' => '2023-02-01',
            'primary_device' => Device::DESKTOP,
            'source_created_at' => '2023-02-01 14:30:00',
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
            'user_id' => $user1->id,
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
    }

    public function test_can_get_users_list()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'first_name', 'last_name', 'email', 'country']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total']
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_get_single_user()
    {
        $user = User::first();
        
        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'first_name', 'last_name', 'email', 'reviews', 'reviewed_movies']
            ])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.first_name', $user->first_name);
    }

    public function test_user_not_found()
    {
        $response = $this->getJson('/api/users/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\User] 999999']);
    }
}