<?php

namespace App\Services\DataTransformers;

class MovieDataTransformer extends BaseDataTransformer
{
    protected function getColumnMapping(): array
    {
        return [
            'external_movie_id' => 'movie_id',
            'title' => 'title',
            'content_type' => 'content_type',
            'genre_primary' => 'genre_primary',
            'genre_secondary' => 'genre_secondary',
            'release_year' => 'release_year',
            'duration_minutes' => 'duration_minutes',
            'rating' => 'rating',
            'language' => 'language',
            'country_of_origin' => 'country_of_origin',
            'imdb_rating' => 'imdb_rating',
            'production_budget' => 'production_budget',
            'box_office_revenue' => 'box_office_revenue',
            'number_of_seasons' => 'number_of_seasons',
            'number_of_episodes' => 'number_of_episodes',
            'is_netflix_original' => 'is_netflix_original',
            'added_to_platform' => 'added_to_platform',
            'content_warning' => 'content_warning',
        ];
    }

    protected function transformValue(string $column, string $value): mixed
    {
        if (empty($value) && $value !== '0') {
            return $this->getDefaultValue($column);
        }

        switch ($column) {
            case 'external_movie_id':
            case 'title':
            case 'content_type':
            case 'genre_primary':
            case 'genre_secondary':
            case 'rating':
            case 'language':
            case 'country_of_origin':
            case 'content_warning':
                return $this->normalizeString($value);
                
            case 'release_year':
                return $this->parseInteger($value);
                
            case 'duration_minutes':
            case 'number_of_seasons':
            case 'number_of_episodes':
                return $this->parseInteger($value);
                
            case 'imdb_rating':
            case 'production_budget':
            case 'box_office_revenue':
                return $this->parseFloat($value);
                
            case 'is_netflix_original':
                return $this->normalizeBoolean($value);
                
            case 'added_to_platform':
                return $this->normalizeDate($value);
                
            default:
                return $value;
        }
    }

    protected function getDefaultValue(string $field, $default = null)
    {
        return match($field) {
            'is_netflix_original' => false,
            default => $default
        };
    }

    public function validate(array $data): bool
    {
        $this->validationErrors = [];
        
        // Required fields (using transformed field names for validation)
        $requiredFields = ['external_movie_id', 'title', 'content_type', 'genre_primary', 'release_year', 'language', 'country_of_origin'];
        $this->validateRequiredFields($data, $requiredFields);
        
        // Validate release year
        $this->validateNumericRange($data['release_year'] ?? null, 1888, date('Y') + 5, 'Release year');
        
        // Validate IMDB rating
        $this->validateNumericRange($data['imdb_rating'] ?? null, 0, 10, 'IMDB rating');
        
        // Validate duration
        $this->validateNumericRange($data['duration_minutes'] ?? null, 0, 10000, 'Duration');
        
        return empty($this->validationErrors);
    }
}
