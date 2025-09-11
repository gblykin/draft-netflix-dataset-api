<?php

namespace App\Services\DataTransformers;

use App\Contracts\DataTransformerInterface;
use Carbon\Carbon;

abstract class BaseDataTransformer implements DataTransformerInterface
{
    protected array $validationErrors = [];

    /**
     * Transform raw data from CSV to database format
     */
    public function transform(array $rawData, array $headers): array
    {
        $this->validationErrors = [];
        $transformedData = [];

        foreach ($this->getColumnMapping() as $targetColumn => $possibleColumns) {
            $value = $this->findColumnValue($rawData, $possibleColumns);
            
            if ($value !== null) {
                $transformedData[$targetColumn] = $this->transformValue($targetColumn, $value);
            }
        }

        return $transformedData;
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Find column value from possible column names
     */
    protected function findColumnValue(array $data, $possibleColumns): ?string
    {
        // Handle both array of possible columns and direct column mapping
        $columns = is_array($possibleColumns) ? $possibleColumns : [$possibleColumns];
        
        foreach ($columns as $column) {
            if (isset($data[$column]) && $data[$column] !== '') {
                return $data[$column];
            }
        }
        return null;
    }

    /**
     * Transform individual field values - to be implemented by child classes
     */
    abstract protected function transformValue(string $column, string $value): mixed;

    /**
     * Get column mapping - to be implemented by child classes
     */
    abstract protected function getColumnMapping(): array;

    /**
     * Normalize boolean values (unified method)
     */
    protected function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim($value));
        
        return match($value) {
            'true', '1', 'yes', 'y', 'on' => true,
            'false', '0', 'no', 'n', 'off', '' => false,
            default => (bool) $value
        };
    }

    /**
     * Parse boolean values (alias for normalizeBoolean for backward compatibility)
     */
    protected function parseBoolean($value): bool
    {
        return $this->normalizeBoolean($value);
    }

    /**
     * Normalize date values (unified method)
     */
    protected function normalizeDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            // Try various date formats
            $formats = [
                'Y-m-d',
                'm/d/Y',
                'd/m/Y',
                'Y-m-d H:i:s',
                'm/d/Y H:i:s',
            ];

            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $value);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            }

            // Try Carbon for more flexible parsing
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->validationErrors[] = "Could not parse date: {$value}";
            return null;
        }
    }

    /**
     * Parse date values (alias for normalizeDate for backward compatibility)
     */
    protected function parseDate($value): ?string
    {
        return $this->normalizeDate($value);
    }

    /**
     * Normalize gender values
     */
    protected function normalizeGender(string $gender): ?string
    {
        // Handle empty or null values
        if (empty(trim($gender))) {
            return null;
        }
        
        $gender = strtolower(trim($gender));
        
        return match($gender) {
            'm', 'male' => 'Male',
            'f', 'female' => 'Female',
            'prefer not to say', 'pnts' => 'Prefer not to say',
            'other', 'o' => 'Other',
            default => 'Other' // Fallback for any unrecognized values
        };
    }

    /**
     * Normalize string values (trim and clean)
     */
    protected function normalizeString(string $value): string
    {
        return trim($value);
    }

    /**
     * Normalize title case strings
     */
    protected function normalizeTitleCase(string $value): string
    {
        return ucwords(strtolower(trim($value)));
    }

    /**
     * Normalize capitalized strings
     */
    protected function normalizeCapitalized(string $value): string
    {
        return ucfirst(strtolower(trim($value)));
    }

    /**
     * Normalize lowercase strings
     */
    protected function normalizeLowercase(string $value): string
    {
        return strtolower(trim($value));
    }

    /**
     * Parse numeric value (integer)
     */
    protected function parseInteger($value): ?int
    {
        if (empty($value) && $value !== '0') {
            return null;
        }
        
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Parse numeric value (float)
     */
    protected function parseFloat($value): ?float
    {
        if (empty($value) && $value !== '0') {
            return null;
        }
        
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Parse currency value (remove $ and commas)
     */
    protected function parseCurrency($value): ?float
    {
        if (empty($value) && $value !== '0') {
            return null;
        }
        
        $cleaned = str_replace(['$', ','], '', $value);
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Validate required fields
     */
    protected function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $this->validationErrors[] = "Required field '{$field}' is missing or empty";
            }
        }
    }

    /**
     * Validate email format
     */
    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate numeric range
     */
    protected function validateNumericRange($value, float $min, float $max, string $fieldName): bool
    {
        if ($value === null || $value === '') {
            return true; // Optional fields
        }
        
        if (!is_numeric($value)) {
            $this->validationErrors[] = "{$fieldName} must be numeric";
            return false;
        }
        
        if ($value < $min || $value > $max) {
            $this->validationErrors[] = "{$fieldName} must be between {$min} and {$max}";
            return false;
        }
        
        return true;
    }

    /**
     * Validate positive integer
     */
    protected function validatePositiveInteger($value, string $fieldName): bool
    {
        if ($value === null || $value === '') {
            return true; // Optional fields
        }
        
        if (!is_numeric($value) || $value < 0) {
            $this->validationErrors[] = "{$fieldName} must be a positive number";
            return false;
        }
        
        return true;
    }

    /**
     * Get default value for a field
     */
    protected function getDefaultValue(string $field, $default = null)
    {
        return $default;
    }

    /**
     * Normalize device value to match enum values.
     */
    protected function normalizeDevice(string $value): string
    {
        $value = trim($value);
        
        // Map common variations to exact enum values
        $mapping = [
            'mobile' => 'Mobile',
            'desktop' => 'Desktop',
            'tablet' => 'Tablet',
            'tv' => 'TV',
            'smart tv' => 'Smart TV',
            'smart_tv' => 'Smart TV',
            'gaming console' => 'Gaming Console',
            'gaming_console' => 'Gaming Console',
            'laptop' => 'Laptop',
            'other' => 'Other',
        ];
        
        $lowerValue = strtolower($value);
        return $mapping[$lowerValue] ?? $value;
    }

    /**
     * Normalize subscription plan value to match enum values.
     */
    protected function normalizeSubscriptionPlan(string $value): string
    {
        $value = trim($value);
        
        // Map common variations to exact enum values
        $mapping = [
            'basic' => 'Basic',
            'standard' => 'Standard', 
            'premium' => 'Premium',
            'premium+' => 'Premium+',
            'premium plus' => 'Premium+',
            'premium_plus' => 'Premium+',
        ];
        
        $lowerValue = strtolower($value);
        return $mapping[$lowerValue] ?? $value;
    }

    /**
     * Normalize content type value to match enum values.
     */
    protected function normalizeContentType(string $value): string
    {
        $value = trim($value);
        
        // Map common variations to exact enum values
        $mapping = [
            'movie' => 'Movie',
            'tv series' => 'TV Series',
            'tv_series' => 'TV Series',
            'documentary' => 'Documentary',
            'stand-up comedy' => 'Stand-up Comedy',
            'stand_up_comedy' => 'Stand-up Comedy',
            'standup comedy' => 'Stand-up Comedy',
            'limited series' => 'Limited Series',
            'limited_series' => 'Limited Series',
        ];
        
        $lowerValue = strtolower($value);
        return $mapping[$lowerValue] ?? $value;
    }

    /**
     * Normalize sentiment value to match enum values.
     */
    protected function normalizeSentiment(string $value): string
    {
        $value = trim($value);
        
        // Map common variations to exact enum values
        $mapping = [
            'positive' => 'positive',
            'negative' => 'negative',
            'neutral' => 'neutral',
        ];
        
        $lowerValue = strtolower($value);
        return $mapping[$lowerValue] ?? $value;
    }
}
