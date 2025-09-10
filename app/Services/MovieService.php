<?php

namespace App\Services;

use App\Models\Movie;
use App\Services\Filters\MovieFilterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MovieService
{
    public function getFilteredMovies(array $filters = []): LengthAwarePaginator
    {
        $query = Movie::query();
        $filterService = new MovieFilterService($query, $filters);
        
        return $filterService->apply();
    }

    public function getMovieById(string $id): Movie
    {
        $movie = Movie::with(['reviews.user'])
            ->where('id', $id)
            ->first();
            
        if (!$movie) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }
        
        return $movie;
    }
}

