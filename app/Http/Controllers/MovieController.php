<?php

namespace App\Http\Controllers;

use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MovieController extends Controller
{
    public function __construct(
        private MovieService $movieService
    ) {}

    /**
     * Display a listing of movies with filtering and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $movies = $this->movieService->getFilteredMovies($request);
        return MovieResource::collection($movies);
    }

    /**
     * Display the specified movie with its reviews and users.
     */
    public function show(string $id): MovieResource
    {
        $movie = $this->movieService->getMovieById($id);
        return new MovieResource($movie);
    }
}

