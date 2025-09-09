<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Pagination
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            
            // Sorting
            'sort_by' => 'sometimes|string|in:name,email,city,subscription_plan,created_at,source_created_at',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            
            // Filtering
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'city' => 'sometimes|string|max:255',
            'subscription_plan' => 'sometimes|string|in:basic,premium,family',
            'gender' => 'sometimes|string|in:male,female,other',
            'age_min' => 'sometimes|integer|min:0|max:120',
            'age_max' => 'sometimes|integer|min:0|max:120|gte:age_min',
            'household_size_min' => 'sometimes|integer|min:1|max:20',
            'household_size_max' => 'sometimes|integer|min:1|max:20|gte:household_size_min',
            'subscription_start_date_from' => 'sometimes|date',
            'subscription_start_date_to' => 'sometimes|date|after_or_equal:subscription_start_date_from',
            'created_at_from' => 'sometimes|date',
            'created_at_to' => 'sometimes|date|after_or_equal:created_at_from',
            'source_created_at_from' => 'sometimes|date',
            'source_created_at_to' => 'sometimes|date|after_or_equal:source_created_at_from',
            
            // Review-related filters
            'exclude_reviewed_movies' => 'sometimes|boolean',
            'show_all_reviewed_movies' => 'sometimes|boolean',
            'has_reviews' => 'sometimes|boolean',
            'min_reviews' => 'sometimes|integer|min:0',
            'max_reviews' => 'sometimes|integer|min:0|gte:min_reviews',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.max' => 'The per page value may not be greater than 100.',
            'sort_by.in' => 'The sort by field must be one of: name, email, city, subscription_plan, created_at, source_created_at.',
            'sort_direction.in' => 'The sort direction must be either asc or desc.',
            'subscription_plan.in' => 'The subscription plan must be one of: basic, premium, family.',
            'gender.in' => 'The gender must be one of: male, female, other.',
            'age_max.gte' => 'The maximum age must be greater than or equal to the minimum age.',
            'household_size_max.gte' => 'The maximum household size must be greater than or equal to the minimum household size.',
            'subscription_start_date_to.after_or_equal' => 'The subscription start date to must be after or equal to the from date.',
            'created_at_to.after_or_equal' => 'The created at to must be after or equal to the from date.',
            'source_created_at_to.after_or_equal' => 'The source created at to must be after or equal to the from date.',
            'max_reviews.gte' => 'The maximum reviews must be greater than or equal to the minimum reviews.',
        ];
    }

    public function validationData(): array 
    {
        return $this->all();        
    }
}
