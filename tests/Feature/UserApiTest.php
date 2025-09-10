<?php

namespace Tests\Feature;

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
        User::create([
            'external_user_id' => 'test_user_1',
            'email' => 'test1@example.com',
            'first_name' => 'Test',
            'last_name' => 'User1',
            'age' => 25,
            'gender' => 'Male',
            'country' => 'USA',
            'state_province' => 'New York',
            'city' => 'New York',
            'subscription_plan' => 'premium',
            'subscription_start_date' => '2023-01-01',
            'is_active' => true,
            'monthly_spend' => 15.99,
            'primary_device' => 'Mobile',
            'household_size' => 1,
            'source_created_at' => '2023-01-01 10:00:00',
        ]);

        User::create([
            'external_user_id' => 'test_user_2',
            'email' => 'test2@example.com',
            'first_name' => 'Test',
            'last_name' => 'User2',
            'age' => 30,
            'gender' => 'Female',
            'country' => 'Canada',
            'state_province' => 'Ontario',
            'city' => 'Toronto',
            'subscription_plan' => 'basic',
            'subscription_start_date' => '2023-02-01',
            'is_active' => false,
            'monthly_spend' => 9.99,
            'primary_device' => 'Desktop',
            'household_size' => 2,
            'source_created_at' => '2023-02-01 10:00:00',
        ]);

        // Create test movies
        $movie1 = Movie::create([
            'external_movie_id' => 'test_movie_1',
            'title' => 'Test Movie 1',
            'content_type' => 'Movie',
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
            'added_to_platform' => '2023-01-01',
        ]);

        $movie2 = Movie::create([
            'external_movie_id' => 'test_movie_2',
            'title' => 'Test Movie 2',
            'content_type' => 'Movie',
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
            'device_type' => 'Mobile',
            'is_verified_watch' => true,
            'helpful_votes' => 8,
            'total_votes' => 10,
            'review_text' => 'Great movie!',
            'sentiment' => 'positive',
            'sentiment_score' => 0.8,
        ]);

        Review::create([
            'external_review_id' => 'test_review_2',
            'user_id' => 1, // Use internal user ID
            'movie_id' => 2, // Use internal movie ID
            'rating' => 4,
            'review_date' => '2023-07-01',
            'device_type' => 'Desktop',
            'is_verified_watch' => false,
            'helpful_votes' => 5,
            'total_votes' => 8,
            'review_text' => 'Good movie!',
            'sentiment' => 'positive',
            'sentiment_score' => 0.6,
        ]);
    }

    public function test_can_get_users_list()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'external_user_id',
                             'email',
                             'first_name',
                             'last_name',
                             'full_name',
                             'age',
                             'gender',
                             'country',
                             'subscription_plan',
                             'is_active',
                             'reviewed_movies'
                         ]
                     ],
                     'links',
                     'meta'
                 ]);
    }

    public function test_can_filter_users_by_boolean_parameters()
    {
        // Test is_active=true
        $response = $this->getJson('/api/users?is_active=true');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_active']);

        // Test is_active=false
        $response = $this->getJson('/api/users?is_active=false');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertFalse($data[0]['is_active']);
    }

    public function test_can_filter_users_by_review_parameters()
    {
        // Test exclude_reviewed_movies=true
        $response = $this->getJson('/api/users?exclude_reviewed_movies=true');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(2, $data);
        // Should not have reviewed_movies in response
        $this->assertArrayNotHasKey('reviewed_movies', $data[0]);

        // Test show_all_reviewed_movies=true
        $response = $this->getJson('/api/users?show_all_reviewed_movies=true');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(2, $data);
        // Should have reviewed_movies in response
        $this->assertArrayHasKey('reviewed_movies', $data[0]);
    }

    public function test_boolean_parameter_validation_works()
    {
        // Test invalid boolean value
        $response = $this->getJson('/api/users?is_active=invalid');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['is_active']);
    }

    public function test_can_combine_multiple_filters()
    {
        $response = $this->getJson('/api/users?is_active=true&subscription_plan=premium');
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_active']);
        $this->assertEquals('premium', $data[0]['subscription_plan']);
    }

    public function test_can_get_single_user()
    {
        $user = User::where('external_user_id', 'test_user_1')->first();
        $response = $this->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'external_user_id',
                         'email',
                         'first_name',
                         'last_name',
                         'reviews'
                     ]
                 ]);
    }

    public function test_user_not_found()
    {
        $response = $this->getJson('/api/users/999999');

        $response->assertStatus(404);
    }
}
