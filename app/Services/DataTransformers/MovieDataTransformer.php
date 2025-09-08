<?php

namespace App\Services\DataTransformers;

use App\Contracts\DataTransformerInterface;

class MovieDataTransformer implements DataTransformerInterface
{
    /**
     * Validation errors
     */
    private array $validationErrors = [];

    /**
     * Map CSV column names to database field names
     */
    private array $columnMapping = [
        'movie_id' => 'movie_id',
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

    /**
     * Transform raw data from CSV to database format
     */
    public function transform(array $rawData, array $headers): array
    {
        $transformed = [];
        
        foreach ($this->columnMapping as $csvColumn => $dbField) {
            $value = $rawData[$csvColumn] ?? null;
            $transformed[$dbField] = $this->transformValue($dbField, $value);
        }
        
        return $transformed;
    }

    /**
     * Transform individual field values
     */
    private function transformValue(string $field, $value)
    {
        if (empty($value) && $value !== '0') {
            return $this->getDefaultValue($field);
        }

        switch ($field) {
            case 'movie_id':
            case 'title':
            case 'content_type':
            case 'genre_primary':
            case 'genre_secondary':
            case 'rating':
            case 'language':
            case 'country_of_origin':
            case 'content_warning':
                return trim($value);
                
            case 'release_year':
                return (int) $value;
                
            case 'duration_minutes':
            case 'number_of_seasons':
            case 'number_of_episodes':
                return is_numeric($value) ? (int) $value : null;
                
            case 'imdb_rating':
            case 'production_budget':
            case 'box_office_revenue':
                return is_numeric($value) ? (float) $value : null;
                
            case 'is_netflix_original':
                return $this->normalizeBoolean($value);
                
            case 'added_to_platform':
                return $this->normalizeDate($value);
                
            default:
                return $value;
        }
    }

    /**
     * Get default value for a field
     */
    private function getDefaultValue(string $field)
    {
        switch ($field) {
            case 'is_netflix_original':
                return false;
            default:
                return null;
        }
    }

    /**
     * Normalize boolean values
     */
    private function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim($value));
        
        return in_array($value, ['true', '1', 'yes', 'on']);
    }

    /**
     * Normalize date values
     */
    private function normalizeDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            $date = new \DateTime($value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate transformed data
     */
    public function validate(array $data): bool
    {
        $errors = [];
        
        // Required fields
        $requiredFields = ['movie_id', 'title', 'content_type', 'genre_primary', 'release_year', 'language', 'country_of_origin'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        // Validate release year
        if (isset($data['release_year']) && ($data['release_year'] < 1888 || $data['release_year'] > date('Y') + 5)) {
            $errors[] = "Release year must be between 1888 and " . (date('Y') + 5);
        }
        
        // Validate IMDB rating
        if (isset($data['imdb_rating']) && $data['imdb_rating'] !== null) {
            if ($data['imdb_rating'] < 0 || $data['imdb_rating'] > 10) {
                $errors[] = "IMDB rating must be between 0 and 10";
            }
        }
        
        // Validate duration
        if (isset($data['duration_minutes']) && $data['duration_minutes'] !== null) {
            if ($data['duration_minutes'] < 0 || $data['duration_minutes'] > 10000) {
                $errors[] = "Duration must be between 0 and 10000 minutes";
            }
        }
        
        $this->validationErrors = $errors;
        return empty($errors);
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Get the column mapping
     */
    public function getColumnMapping(): array
    {
        return $this->columnMapping;
    }
}
