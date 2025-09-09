<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MovieFilterService extends BaseFilterService
{
    protected function applyFilters(): void
    {
        // Support 'genre' parameter that searches both genre_primary and genre_secondary
        if (isset($this->filters['genre'])) {
            $genre = $this->filters['genre'];
            $this->query->where(function ($query) use ($genre) {
                $query->where('genre_primary', 'like', '%' . $genre . '%')
                      ->orWhere('genre_secondary', 'like', '%' . $genre . '%');
            });
        }

        // Support 'genre_primary' parameter for backward compatibility
        if (isset($this->filters['genre_primary'])) {
            $this->query->where('genre_primary', 'like', '%' . $this->filters['genre_primary'] . '%');
        }

        if (isset($this->filters['genre_secondary'])) {
            $this->query->where('genre_secondary', 'like', '%' . $this->filters['genre_secondary'] . '%');
        }

        if (isset($this->filters['content_type'])) {
            $this->query->where('content_type', 'like', '%' . $this->filters['content_type'] . '%');
        }

        if (isset($this->filters['release_year'])) {
            $this->query->where('release_year', $this->filters['release_year']);
        }

        if (isset($this->filters['rating'])) {
            $this->query->where('rating', $this->filters['rating']);
        }

        if (isset($this->filters['country_of_origin'])) {
            $this->query->where('country_of_origin', 'like', '%' . $this->filters['country_of_origin'] . '%');
        }

        if (isset($this->filters['language'])) {
            $this->query->where('language', 'like', '%' . $this->filters['language'] . '%');
        }

        if (isset($this->filters['is_netflix_original'])) {
            $this->query->where('is_netflix_original', (bool) $this->filters['is_netflix_original']);
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

