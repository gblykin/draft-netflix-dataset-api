<?php

namespace App\Services\DataTransformers;

class UserDataTransformer extends BaseDataTransformer
{
    protected function getColumnMapping(): array
    {
        return [
            'external_user_id' => 'user_id',
            'email' => 'email',
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
            'source_created_at' => ['created_at'],
        ];
    }

    public function validate(array $data): bool
    {
        $this->validationErrors = [];

        // Required fields validation
        $requiredFields = ['external_user_id', 'email', 'first_name', 'last_name', 'country', 'city', 'subscription_plan'];
        $this->validateRequiredFields($data, $requiredFields);

        // Age validation (optional but if present must be valid)
        $this->validateNumericRange($data['age'] ?? null, 0, 150, 'Age');

        // Gender validation (optional field with specific allowed values)
        if (isset($data['gender']) && $data['gender'] !== '' && $data['gender'] !== null) {
            $validGenderValues = ['male', 'female', 'prefer not to say', 'other', 'm', 'f', 'pnts'];
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
        if (isset($data['email']) && !$this->validateEmail($data['email'])) {
            $this->validationErrors[] = "Email must be a valid email address";
        }

        // Household size validation (optional)
        $this->validatePositiveInteger($data['household_size'] ?? null, 'Household size');

        return empty($this->validationErrors);
    }

    protected function transformValue(string $column, string $value): mixed
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
                return $this->parseInteger($value);

            case 'gender':
                return $this->normalizeGender($value);

            case 'monthly_spend':
                return $this->parseCurrency($value);

            case 'subscription_start_date':
                return $this->parseDate($value);

            case 'is_active':
                return $this->parseBoolean($value);

            case 'subscription_plan':
            case 'primary_device':
                return $this->normalizeCapitalized($value);

            case 'email':
                return $this->normalizeLowercase($value);

            case 'first_name':
            case 'last_name':
            case 'city':
            case 'state_province':
            case 'country':
                return $this->normalizeTitleCase($value);

            default:
                return $this->normalizeString($value);
        }
    }
}
