<?php

namespace App\Services\DataTransformers;

use App\Contracts\DataTransformerInterface;

class ReviewDataTransformer implements DataTransformerInterface
{
    /**
     * Validation errors
     */
    private array $validationErrors = [];

    /**
     * Map CSV column names to database field names
     */
    private array $columnMapping = [
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
            case 'review_id':
            case 'user_id':
            case 'movie_id':
            case 'device_type':
            case 'review_text':
            case 'sentiment':
                return trim($value);
                
            case 'rating':
            case 'helpful_votes':
            case 'total_votes':
                return is_numeric($value) ? (int) $value : null;
                
            case 'sentiment_score':
                return is_numeric($value) ? (float) $value : null;
                
            case 'is_verified_watch':
                return $this->normalizeBoolean($value);
                
            case 'review_date':
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
            case 'is_verified_watch':
                return false;
            case 'helpful_votes':
            case 'total_votes':
                return 0;
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
        $requiredFields = ['review_id', 'user_id', 'movie_id', 'rating', 'review_date', 'device_type'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        // Validate rating
        if (isset($data['rating']) && ($data['rating'] < 1 || $data['rating'] > 5)) {
            $errors[] = "Rating must be between 1 and 5";
        }
        
        // Validate sentiment score
        if (isset($data['sentiment_score']) && $data['sentiment_score'] !== null) {
            if ($data['sentiment_score'] < -1.0 || $data['sentiment_score'] > 1.0) {
                $errors[] = "Sentiment score must be between -1.0 and 1.0";
            }
        }
        
        // Validate votes
        if (isset($data['helpful_votes']) && $data['helpful_votes'] !== null) {
            if ($data['helpful_votes'] < 0) {
                $errors[] = "Helpful votes cannot be negative";
            }
        }
        
        if (isset($data['total_votes']) && $data['total_votes'] !== null) {
            if ($data['total_votes'] < 0) {
                $errors[] = "Total votes cannot be negative";
            }
        }
        
        // Validate helpful_votes <= total_votes
        if (isset($data['helpful_votes']) && isset($data['total_votes']) && 
            $data['helpful_votes'] !== null && $data['total_votes'] !== null) {
            if ($data['helpful_votes'] > $data['total_votes']) {
                $errors[] = "Helpful votes cannot exceed total votes";
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
