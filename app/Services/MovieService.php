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

}

