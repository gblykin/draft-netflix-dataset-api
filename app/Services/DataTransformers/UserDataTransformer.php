<?php

namespace App\Services\DataTransformers;

use App\Contracts\DataTransformerInterface;
use Carbon\Carbon;

class UserDataTransformer implements DataTransformerInterface
{
    private array $validationErrors = [];

    private array $columnMapping = [
        'user_id' => ['user_id'],
        'email' => ['email'],
        'first_name' => ['first_name'],
        'last_name' => ['last_name'],
        'age' => ['age'],
        'gender' => ['gender'],
        'country' => ['country'],
        'state_province' => ['state_province'],
        'city' => ['city'],
        'subscription_plan' => ['subscription_plan'],
        'subscription_start_date' => ['subscription_start_date'],
        'is_active' => ['is_active'],
        'monthly_spend' => ['monthly_spend'],
        'primary_device' => ['primary_device'],
        'household_size' => ['household_size'],
    ];

    public function transform(array $rawData, array $headers): array
    {
        $this->validationErrors = [];
        $transformedData = [];

        foreach ($this->columnMapping as $targetColumn => $possibleColumns) {
            $value = $this->findColumnValue($rawData, $possibleColumns);
            
            if ($value !== null) {
                $transformedData[$targetColumn] = $this->transformValue($targetColumn, $value);
            }
        }

        return $transformedData;
    }

    public function validate(array $data): bool
    {
        $this->validationErrors = [];

        // Required fields validation (gender, monthly_spend, household_size, primary_device now optional)
        $requiredFields = ['user_id', 'email', 'first_name', 'last_name', 'country', 'city', 'subscription_plan'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $this->validationErrors[] = "Required field '{$field}' is missing or empty";
            }
        }

        // Age validation (optional but if present must be valid)
        if (isset($data['age']) && $data['age'] !== '' && $data['age'] !== null) {
            if (!is_numeric($data['age']) || $data['age'] < 0 || $data['age'] > 150) {
                $this->validationErrors[] = "Age must be a number between 0 and 150";
            }
        }

        // Gender validation (optional field with specific allowed values)
        if (isset($data['gender']) && $data['gender'] !== '' && $data['gender'] !== null) {
            $validGenderValues = ['male', 'female', 'prefer not to say', 'other', 'm', 'f', 'prefer not to say', 'pnts'];
            if (!in_array(strtolower(trim($data['gender'])), $validGenderValues)) {
                $this->validationErrors[] = "Gender must be Male, Female, Prefer not to say, Other, or empty";
            }
        }

        // Monthly spend validation (optional)
        if (isset($data['monthly_spend']) && $data['monthly_spend'] !== '' && $data['monthly_spend'] !== null) {
            if (!is_numeric($data['monthly_spend'])) {
                $this->validationErrors[] = "Monthly spend must be numeric";
            }
        }

        // Email validation (required and must be valid)
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->validationErrors[] = "Email must be a valid email address";
        }

        // Household size validation (optional)
        if (isset($data['household_size']) && $data['household_size'] !== '' && $data['household_size'] !== null) {
            if (!is_numeric($data['household_size']) || $data['household_size'] < 1) {
                $this->validationErrors[] = "Household size must be a positive number";
            }
        }

        return empty($this->validationErrors);
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    private function findColumnValue(array $data, array $possibleColumns): ?string
    {
        foreach ($possibleColumns as $column) {
            if (isset($data[$column]) && $data[$column] !== '') {
                return $data[$column];
            }
        }
        return null;
    }

    private function transformValue(string $column, string $value): mixed
    {
        // Handle empty values
        if ($value === '' || $value === null) {
            return match($column) {
                'age' => null,
                'household_size' => null,
                'monthly_spend' => null,
                'gender' => null,
                'primary_device' => null,
                default => $value
            };
        }

        switch ($column) {
            case 'age':
            case 'household_size':
                return (int) $value;

            case 'gender':
                return $this->normalizeGender($value);

            case 'monthly_spend':
                return (float) str_replace(['$', ','], '', $value);

            case 'subscription_start_date':
                return $this->parseDate($value);

            case 'is_active':
                return $this->parseBoolean($value);

            case 'subscription_plan':
            case 'primary_device':
                return ucfirst(strtolower(trim($value)));

            case 'email':
                return strtolower(trim($value));

            case 'first_name':
            case 'last_name':
            case 'city':
            case 'state_province':
            case 'country':
                return ucwords(strtolower(trim($value)));

            default:
                return trim($value);
        }
    }

    private function normalizeGender(string $gender): ?string
    {
        // Handle empty or null values
        if (empty(trim($gender))) {
            return null;
        }
        
        $gender = strtolower(trim($gender));
        
        return match($gender) {
            'm', 'male' => 'Male',
            'f', 'female' => 'Female',
            'prefer not to say', 'pnts', 'prefer not to say' => 'Prefer not to say',
            'other', 'o' => 'Other',
            default => 'Other' // Fallback for any unrecognized values
        };
    }

    private function parseDate(string $dateString): ?string
    {
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
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            }

            // Try Carbon for more flexible parsing
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->validationErrors[] = "Could not parse date: {$dateString}";
            return null;
        }
    }

    private function parseBoolean(string $value): bool
    {
        $value = strtolower(trim($value));
        
        return match($value) {
            'true', '1', 'yes', 'y', 'on' => true,
            'false', '0', 'no', 'n', 'off', '' => false,
            default => (bool) $value
        };
    }
}
