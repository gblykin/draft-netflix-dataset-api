<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MovieFilterService extends BaseFilterService
{
    protected function applyFilters(): void
    {
        // Support 'genre' parameter that searches both genre_primary and genre_secondary
        if ($this->request->has('genre')) {
            $genre = $this->request->input('genre');
            $this->query->where(function ($query) use ($genre) {
                $query->where('genre_primary', 'like', '%' . $genre . '%')
                      ->orWhere('genre_secondary', 'like', '%' . $genre . '%');
            });
        }

        // Support 'genre_primary' parameter for backward compatibility
        if ($this->request->has('genre_primary')) {
            $this->query->where('genre_primary', 'like', '%' . $this->request->input('genre_primary') . '%');
        }

        if ($this->request->has('genre_secondary')) {
            $this->query->where('genre_secondary', 'like', '%' . $this->request->input('genre_secondary') . '%');
        }

        if ($this->request->has('content_type')) {
            $this->query->where('content_type', 'like', '%' . $this->request->input('content_type') . '%');
        }

        if ($this->request->has('release_year')) {
            $this->query->where('release_year', $this->request->input('release_year'));
        }

        if ($this->request->has('rating')) {
            $this->query->where('rating', $this->request->input('rating'));
        }

        if ($this->request->has('country_of_origin')) {
            $this->query->where('country_of_origin', 'like', '%' . $this->request->input('country_of_origin') . '%');
        }

        if ($this->request->has('language')) {
            $this->query->where('language', 'like', '%' . $this->request->input('language') . '%');
        }

        if ($this->request->has('is_netflix_original')) {
            $this->query->where('is_netflix_original', $this->request->boolean('is_netflix_original'));
        }
    }

    protected function getSortableFields(): array
    {
        return ['title', 'release_year', 'duration_minutes', 'production_budget', 'box_office_revenue', 'imdb_rating'];
    }

    protected function getDefaultSortField(): string
    {
        return 'title';
    }
}

