<?php

namespace App\Services;

use App\Models\Review;
use App\Services\Filters\ReviewFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function getFilteredReviews(array $filters = []): LengthAwarePaginator
    {
        $query = Review::query();
        $filterService = new ReviewFilterService($query, $filters);
        
        return $filterService->apply();
    }

    public function getReviewById(string $id): Review
    {
        return Review::with(['user', 'movie'])
            ->where('id', $id)
            ->firstOrFail();
    }

    public function createReview(array $data): Review
    {
        $review = Review::create($data);
        $review->load(['user', 'movie']);
        return $review;
    }

    public function updateReview(string $id, array $data): Review
    {
        $review = Review::where('id', $id)
            ->firstOrFail();

        $review->update($data);
        $review->load(['user', 'movie']);
        return $review;
    }

    public function deleteReview(string $id): bool
    {
        $review = Review::where('id', $id)
            ->firstOrFail();

        return $review->delete();
    }

    public function getReviewsByUser(string $userId): LengthAwarePaginator
    {
        return Review::where('user_id', $userId)
            ->with(['movie'])
            ->paginate(15);
    }

    public function getReviewsByMovie(string $movieId): LengthAwarePaginator
    {
        return Review::where('movie_id', $movieId)
            ->with(['user'])
            ->paginate(15);
    }

    public function getHighRatedReviews(int $minRating = 4): LengthAwarePaginator
    {
        return Review::where('rating', '>=', $minRating)
            ->with(['user', 'movie'])
            ->paginate(15);
    }

    public function getVerifiedReviews(): LengthAwarePaginator
    {
        return Review::where('is_verified_watch', true)
            ->with(['user', 'movie'])
            ->paginate(15);
    }
}
