<?php

namespace App\Services\DataTransformers;

use App\Models\User;
use App\Models\Movie;

class ReviewDataTransformer extends BaseDataTransformer
{
    protected function getColumnMapping(): array
    {
        return [
            'external_review_id' => 'review_id',
            'external_user_id' => 'user_id', // External ID from CSV
            'external_movie_id' => 'movie_id', // External ID from CSV
            'rating' => 'rating',
            'review_date' => 'review_date',
            'device_type' => 'device_type',
            'is_verified_watch' => 'is_verified_watch',
            'helpful_votes' => 'helpful_votes',
            'total_votes' => 'total_votes',
            'review_text' => 'review_text',
            'sentiment' => 'sentiment',
            'sentiment_score' => 'sentiment_score',
        ];
    }

    protected function transformValue(string $column, string $value): mixed
    {
        if (empty($value) && $value !== '0') {
            return $this->getDefaultValue($column);
        }

        switch ($column) {
            case 'external_review_id':
            case 'external_user_id':
            case 'external_movie_id':
            case 'review_text':
                return $this->normalizeString($value);
                
            case 'device_type':
                return $this->normalizeDevice($value);
                
            case 'sentiment':
                return $this->normalizeSentiment($value);
                
            case 'rating':
            case 'helpful_votes':
            case 'total_votes':
                return $this->parseInteger($value);
                
            case 'sentiment_score':
                return $this->parseFloat($value);
                
            case 'is_verified_watch':
                return $this->normalizeBoolean($value);
                
            case 'review_date':
                return $this->normalizeDate($value);
                
            default:
                return $value;
        }
    }

    protected function getDefaultValue(string $field, $default = null)
    {
        return match($field) {
            'is_verified_watch' => false,
            'helpful_votes', 'total_votes' => 0,
            default => $default
        };
    }

    public function validate(array $data): bool
    {
        $this->validationErrors = [];
        
        // Required fields
        $requiredFields = ['external_review_id', 'external_user_id', 'external_movie_id', 'rating', 'review_date', 'device_type'];
        $this->validateRequiredFields($data, $requiredFields);
        
        // Validate rating
        $this->validateNumericRange($data['rating'] ?? null, 1, 5, 'Rating');
        
        // Validate sentiment score
        $this->validateNumericRange($data['sentiment_score'] ?? null, -1.0, 1.0, 'Sentiment score');
        
        // Validate votes (must be non-negative)
        $this->validatePositiveInteger($data['helpful_votes'] ?? null, 'Helpful votes');
        $this->validatePositiveInteger($data['total_votes'] ?? null, 'Total votes');
        
        // Validate helpful_votes <= total_votes
        if (isset($data['helpful_votes']) && isset($data['total_votes']) && 
            $data['helpful_votes'] !== null && $data['total_votes'] !== null) {
            if ($data['helpful_votes'] > $data['total_votes']) {
                $this->validationErrors[] = "Helpful votes cannot exceed total votes";
            }
        }
        
        return empty($this->validationErrors);
    }

    /**
     * Transform external IDs to internal IDs for database storage
     */
    public function transformExternalIds(array $data): array
    {
        // Convert external_user_id to internal user_id
        if (isset($data['external_user_id'])) {
            $user = User::where('external_user_id', $data['external_user_id'])->first();
            if ($user) {
                $data['user_id'] = $user->id;
            } else {
                $this->validationErrors[] = "User with external ID '{$data['external_user_id']}' not found";
            }
        }

        // Convert external_movie_id to internal movie_id
        if (isset($data['external_movie_id'])) {
            $movie = Movie::where('external_movie_id', $data['external_movie_id'])->first();
            if ($movie) {
                $data['movie_id'] = $movie->id;
            } else {
                $this->validationErrors[] = "Movie with external ID '{$data['external_movie_id']}' not found";
            }
        }

        return $data;
    }

}
