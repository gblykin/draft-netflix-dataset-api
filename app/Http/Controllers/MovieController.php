<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Http\Resources\MovieResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    /**
     * Display a listing of movies with filtering and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Movie::query();

        // Apply filters
        if ($request->has('genre_primary')) {
            $query->where('genre_primary', 'like', '%' . $request->input('genre_primary') . '%');
        }

        if ($request->has('genre_secondary')) {
            $query->where('genre_secondary', 'like', '%' . $request->input('genre_secondary') . '%');
        }

        if ($request->has('content_type')) {
            $query->where('content_type', 'like', '%' . $request->input('content_type') . '%');
        }

        if ($request->has('release_year')) {
            $query->where('release_year', $request->input('release_year'));
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->input('rating'));
        }

        if ($request->has('country_of_origin')) {
            $query->where('country_of_origin', 'like', '%' . $request->input('country_of_origin') . '%');
        }

        if ($request->has('language')) {
            $query->where('language', 'like', '%' . $request->input('language') . '%');
        }

        if ($request->has('is_netflix_original')) {
            $query->where('is_netflix_original', $request->boolean('is_netflix_original'));
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'title');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if (in_array($sortBy, ['title', 'release_year', 'duration_minutes', 'production_budget', 'box_office_revenue', 'imdb_rating'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100); // Max 100 items per page
        $movies = $query->with('reviews')->paginate($perPage);

        return MovieResource::collection($movies);
    }

    /**
     * Display the specified movie with its reviews and users.
     */
    public function show(string $id): MovieResource
    {
        $movie = Movie::with(['reviews.user', 'reviewers'])
            ->where('movie_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        return new MovieResource($movie);
    }
}

