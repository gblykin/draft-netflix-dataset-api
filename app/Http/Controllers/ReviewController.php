<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Review::query();

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('movie_id')) {
            $query->where('movie_id', $request->input('movie_id'));
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->input('rating'));
        }

        if ($request->has('rating_min')) {
            $query->where('rating', '>=', $request->input('rating_min'));
        }

        if ($request->has('rating_max')) {
            $query->where('rating', '<=', $request->input('rating_max'));
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'review_date');
        $sortOrder = $request->input('sort_order', 'desc');
        
        if (in_array($sortBy, ['review_date', 'rating', 'helpfulness'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100); // Max 100 items per page
        $reviews = $query->with(['user', 'movie'])->paginate($perPage);

        return ReviewResource::collection($reviews);
    }

    /**
     * Display the specified review.
     */
    public function show(string $id): ReviewResource
    {
        $review = Review::with(['user', 'movie'])
            ->where('review_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        return new ReviewResource($review);
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request): ReviewResource|JsonResponse
    {
        try {
            $validated = $request->validate([
                'review_id' => 'required|string|unique:reviews,review_id',
                'user_id' => 'required|string|exists:users,user_id',
                'movie_id' => 'required|string|exists:movies,movie_id',
                'rating' => 'required|integer|min:1|max:5',
                'review_text' => 'nullable|string|max:2000',
                'review_date' => 'required|date',
                'helpfulness' => 'nullable|integer|min:0',
            ]);

            $review = Review::create($validated);
            $review->load(['user', 'movie']);

            return new ReviewResource($review);
        } catch (ValidationException $e) {
            return response()->json([
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
            $review = Review::where('review_id', $id)
                ->orWhere('id', $id)
                ->firstOrFail();

            $validated = $request->validate([
                'rating' => 'sometimes|integer|min:1|max:5',
                'review_text' => 'sometimes|nullable|string|max:2000',
                'helpfulness' => 'sometimes|integer|min:0',
            ]);

            $review->update($validated);
            $review->load(['user', 'movie']);

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
        $review = Review::where('review_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully'
        ], 200);
    }
}

