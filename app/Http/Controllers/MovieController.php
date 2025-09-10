<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieListRequest;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use App\Services\MovieService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    public function __construct(
        private MovieService $movieService
    ) {}

    /**
     * Display a listing of movies with filtering and pagination.
     */
    public function index(MovieListRequest $request): AnonymousResourceCollection
    {
        $requestData = $request->all();
        $movies = $this->movieService->getFilteredMovies($requestData);
        return MovieResource::collection($movies);
    }

    /**
     * Display the specified movie with its reviews and users.
     */
    public function show(string $id): MovieResource|\Illuminate\Http\JsonResponse
    {
        // Validate that ID is numeric
        if (!is_numeric($id)) {
            return response()->json(['message' => 'Movie not found'], 404);
        }

        try {
            $movie = $this->movieService->getMovieById($id);
            return new MovieResource($movie);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Movie not found'], 404);
        }
    }
}

