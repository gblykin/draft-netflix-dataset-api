<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewListRequest;
use App\Http\Resources\ReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    /**
     * Display a listing of reviews.
     */
    public function index(ReviewListRequest $request): AnonymousResourceCollection
    {
        $requestData = $request->all();
        $reviews = $this->reviewService->getFilteredReviews($requestData);
        return ReviewResource::collection($reviews);
    }

    /**
     * Display the specified review.
     */
    public function show(string $id): ReviewResource
    {
        $review = $this->reviewService->getReviewById($id);
        return new ReviewResource($review);
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request): ReviewResource|JsonResource
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'movie_id' => 'required|integer|exists:movies,id',
                'rating' => 'required|integer|min:1|max:5',
                'review_text' => 'nullable|string|max:2000',
                'review_date' => 'required|date',
                'device_type' => 'nullable|string|max:255',
                'is_verified_watch' => 'nullable|boolean',
                'helpful_votes' => 'nullable|integer|min:0',
                'total_votes' => 'nullable|integer|min:0',
                'sentiment' => 'nullable|string|in:positive,negative,neutral',
                'sentiment_score' => 'nullable|numeric|min:-1|max:1',
            ]);

            $review = $this->reviewService->createReview($validated);
            return new ReviewResource($review);
        } catch (ValidationException $e) {
            return JsonResource::make([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, string $id): ReviewResource|JsonResponse
    {
        try {
            $validated = $request->validate([
                'rating' => 'sometimes|integer|min:1|max:5',
                'review_text' => 'sometimes|nullable|string|max:2000',
                'helpful_votes' => 'sometimes|integer|min:0',
                'total_votes' => 'sometimes|integer|min:0',
                'device_type' => 'sometimes|string|max:255',
                'is_verified_watch' => 'sometimes|boolean',
                'sentiment' => 'sometimes|string|in:positive,negative,neutral',
                'sentiment_score' => 'sometimes|numeric|min:-1|max:1',
            ]);

            $review = $this->reviewService->updateReview($id, $validated);
            return new ReviewResource($review);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified review.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->reviewService->deleteReview($id);

        return response()->json([
            'message' => 'Review deleted successfully'
        ], 200);
    }
}