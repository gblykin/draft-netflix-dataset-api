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
        // Create test users
        User::create([
            'external_user_id' => 'test_user_1',
            'email' => 'test1@example.com',
            'first_name' => 'Test',
            'last_name' => 'User1',
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

        User::create([
            'external_user_id' => 'test_user_2',
            'email' => 'test2@example.com',
            'first_name' => 'Test',
            'last_name' => 'User2',
            'age' => 30,
            'gender' => Gender::FEMALE,
            'country' => 'Canada',
            'state_province' => 'Ontario',
            'city' => 'Toronto',
            'subscription_plan' => SubscriptionPlan::BASIC,
            'subscription_start_date' => '2023-02-01',
            'is_active' => false,
            'monthly_spend' => 9.99,
            'primary_device' => Device::DESKTOP,
            'household_size' => 2,
            'source_created_at' => '2023-02-01 10:00:00',
        ]);

        // Create test movies
        $movie1 = Movie::create([
            'external_movie_id' => 'test_movie_1',
            'title' => 'Test Movie 1',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
            'added_to_platform' => '2023-01-01',
        ]);

        $movie2 = Movie::create([
            'external_movie_id' => 'test_movie_2',
            'title' => 'Test Movie 2',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Drama',
            'release_year' => 2022,
            'language' => 'English',
            'country_of_origin' => 'UK',
            'added_to_platform' => '2022-06-01',
        ]);

        // Create test reviews
        Review::create([
            'external_review_id' => 'test_review_1',
            'user_id' => 1, // Use internal user ID
            'movie_id' => 1, // Use internal movie ID
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

        Review::create([
            'external_review_id' => 'test_review_2',
            'user_id' => 2, // Use internal user ID
            'movie_id' => 2, // Use internal movie ID
            'rating' => 4,
            'review_date' => '2023-07-01',
            'device_type' => Device::DESKTOP,
            'is_verified_watch' => false,
            'helpful_votes' => 5,
            'total_votes' => 8,
            'review_text' => 'Good movie!',
            'sentiment' => Sentiment::POSITIVE,
            'sentiment_score' => 0.6,
        ]);
    }

    public function test_can_get_reviews_list()
    {
        $response = $this->getJson('/api/reviews');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'external_review_id',
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
                             'sentiment_score'
                         ]
                     ],
                     'links',
                     'meta'
                 ]);
    }

    public function test_can_filter_reviews_by_boolean_parameters()
    {
        // Test is_verified_watch=true
        $response = $this->getJson('/api/reviews?is_verified_watch=true');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_verified_watch']);

        // Test is_verified_watch=false
        $response = $this->getJson('/api/reviews?is_verified_watch=false');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertFalse($data[0]['is_verified_watch']);
    }

    public function test_boolean_parameter_validation_works()
    {
        // Test invalid boolean value
        $response = $this->getJson('/api/reviews?is_verified_watch=invalid');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_verified_watch']);
    }

    public function test_can_combine_multiple_filters()
    {
        $response = $this->getJson('/api/reviews?is_verified_watch=true&rating=5');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_verified_watch']);
        $this->assertEquals(5, $data[0]['rating']);
    }

    public function test_can_get_single_review()
    {
        $review = Review::where('external_review_id', 'test_review_1')->first();
        $response = $this->getJson('/api/reviews/' . $review->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'external_review_id',
                         'user_id',
                         'movie_id',
                         'rating',
                         'review_text',
                         'movie'
                     ]
                 ]);
    }

    public function test_review_not_found()
    {
        $response = $this->getJson('/api/reviews/999999');

        $response->assertStatus(404);
    }
}
