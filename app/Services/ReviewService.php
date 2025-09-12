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


    public function createReview(array $data): Review
    {
        $review = Review::create($data);
        $review->load(['user', 'movie']);
        return $review;
    }

    public function updateReview(Review $review, array $data): void
    {
        $review->update($data);
    }

    public function deleteReview(Review $review): bool
    {
        return $review->delete();
    }

}
