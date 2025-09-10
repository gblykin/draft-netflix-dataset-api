<?php

namespace Tests\Unit\Services;

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
            'subscription_plan' => 'Basic',
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
            'content_type' => 'Movie',
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
            'device_type' => 'Mobile',
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

    public function test_get_review_by_id_returns_correct_review()
    {
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
        
        $review = $this->createTestReview([
            'external_review_id' => 'test-review-123',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 5,
        ]);

        $result = $this->reviewService->getReviewById($review->id);

        $this->assertEquals($review->id, $result->id);
        $this->assertEquals('test-review-123', $result->external_review_id);
    }

    public function test_create_review_creates_and_returns_review()
    {
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

        $data = [
            'external_review_id' => 'new-review-123',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 5,
            'review_text' => 'Great movie!',
            'review_date' => now()->toDateString(),
            'device_type' => 'Mobile',
        ];

        $result = $this->reviewService->createReview($data);

        $this->assertEquals('new-review-123', $result->external_review_id);
        $this->assertEquals(5, $result->rating);
        $this->assertEquals('Great movie!', $result->review_text);
    }

    public function test_update_review_updates_and_returns_review()
    {
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
        
        $review = $this->createTestReview([
            'external_review_id' => 'test-review-123',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 3,
        ]);

        $data = ['rating' => 5, 'review_text' => 'Updated review'];

        $result = $this->reviewService->updateReview($review->id, $data);

        $this->assertEquals(5, $result->rating);
        $this->assertEquals('Updated review', $result->review_text);
    }

    public function test_delete_review_returns_true()
    {
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
        
        $review = $this->createTestReview([
            'external_review_id' => 'test-review-123',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 5,
        ]);

        $result = $this->reviewService->deleteReview($review->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('reviews', ['external_review_id' => 'test-review-123']);
    }

    public function test_get_reviews_by_user_returns_user_reviews()
    {
        $user = $this->createTestUser([
            'external_user_id' => 'test-user-1',
            'email' => 'user@test.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $movie1 = $this->createTestMovie([
            'external_movie_id' => 'test-movie-1',
            'title' => 'Test Movie 1',
        ]);
        $movie2 = $this->createTestMovie([
            'external_movie_id' => 'test-movie-2',
            'title' => 'Test Movie 2',
            'genre_primary' => 'Comedy',
            'release_year' => 2022,
        ]);

        $this->createTestReview([
            'external_review_id' => 'review-1',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie1->id, // Use internal movie ID
            'rating' => 5,
        ]);
        $this->createTestReview([
            'external_review_id' => 'review-2',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie2->id, // Use internal movie ID
            'rating' => 4,
        ]);

        $result = $this->reviewService->getReviewsByUser($user->id);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }

    public function test_get_high_rated_reviews_returns_reviews_with_min_rating()
    {
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
        $this->createTestReview([
            'external_review_id' => 'review-3',
            'user_id' => $user->id, // Use internal user ID
            'movie_id' => $movie->id, // Use internal movie ID
            'rating' => 3,
        ]);

        $result = $this->reviewService->getHighRatedReviews(4);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }
}