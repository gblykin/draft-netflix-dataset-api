<?php

namespace App\Services\DataTransformers;

class ReviewDataTransformer extends BaseDataTransformer
{
    protected function getColumnMapping(): array
    {
        return [
            'review_id' => 'review_id',
            'user_id' => 'user_id',
            'movie_id' => 'movie_id',
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
            case 'review_id':
            case 'user_id':
            case 'movie_id':
            case 'device_type':
            case 'review_text':
            case 'sentiment':
                return $this->normalizeString($value);
                
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
        $requiredFields = ['review_id', 'user_id', 'movie_id', 'rating', 'review_date', 'device_type'];
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
}
