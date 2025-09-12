<?php

namespace Tests\Unit\Services;

use App\Enums\ContentType;
use App\Enums\Device;
use App\Enums\SubscriptionPlan;
use App\Models\Review;
use App\Models\User;
use App\Models\Movie;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReviewService $reviewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewService = new ReviewService();
    }

    private function createTestUser(array $overrides = []): User
    {
        $defaults = [
            'external_user_id' => 'test-user-' . uniqid(),
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'country' => 'USA',
            'city' => 'New York',
            'subscription_plan' => SubscriptionPlan::BASIC,
            'subscription_start_date' => now()->toDateString(),
            'source_created_at' => now()->subMonths(6),
        ];

        return User::create(array_merge($defaults, $overrides));
    }

    private function createTestMovie(array $overrides = []): Movie
    {
        $defaults = [
            'external_movie_id' => 'test-movie-' . uniqid(),
            'title' => 'Test Movie',
            'content_type' => ContentType::MOVIE,
            'genre_primary' => 'Action',
            'release_year' => 2023,
            'language' => 'English',
            'country_of_origin' => 'USA',
        ];

        return Movie::create(array_merge($defaults, $overrides));
    }

    private function createTestReview(array $overrides = []): Review
    {
        $defaults = [
            'external_review_id' => 'test-review-' . uniqid(),
            'user_id' => 1, // Use internal user ID
            'movie_id' => 1, // Use internal movie ID
            'rating' => 5,
            'review_date' => now()->toDateString(),
            'device_type' => Device::MOBILE,
        ];

        return Review::create(array_merge($defaults, $overrides));
    }

    public function test_get_filtered_reviews_returns_paginated_results()
    {
        // Create test data using helpers
        $user = $this->createTestUser([
            'external_user_id' => 'test-user-1',
            'email' => 'user@test.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $movie = $this->createTestMovie([
            'external_movie_id' => 'test-movie-1',
            'title' => 'Test Movie',
        ]);
        
        $this->createTestReview([
            'external_review_id' => 'review-1',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 5,
        ]);
        $this->createTestReview([
            'external_review_id' => 'review-2',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 4,
        ]);

        $result = $this->reviewService->getFilteredReviews([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }


}