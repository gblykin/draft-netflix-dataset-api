<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewListRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    /**
     * Display a listing of reviews.
     */
    public function index(ReviewListRequest $request)
    {
        $requestData = $request->all();
        $reviews = $this->reviewService->getFilteredReviews($requestData);
        $reviews->load(['user', 'movie']);
        return ReviewResource::collection($reviews);
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review): ReviewResource
    {
        $review->load(['user', 'movie']);
        return new ReviewResource($review);
    }

    /**
     * Store a newly created review.
     */
    public function store(StoreReviewRequest $request): ReviewResource
    {
        $validated = $request->validated();
        $review = $this->reviewService->createReview($validated);
        return new ReviewResource($review);
    }

    /**
     * Update the specified review.
     */
    public function update(UpdateReviewRequest $request, Review $review): ReviewResource
    {
        $validated = $request->validated();
        $this->reviewService->updateReview($review, $validated);
        return new ReviewResource($review->fresh());
    }

    /**
     * Remove the specified review.
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->reviewService->deleteReview($review);

        return response()->json([
            'message' => 'Review deleted successfully'
        ], 200);
    }
}