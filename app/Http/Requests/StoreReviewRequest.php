<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends BaseReviewRequest
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
        $commonRules = $this->getCommonRules();
        
        return array_merge([
            'user_id' => 'required|integer|exists:users,id',
            'movie_id' => 'required|integer|exists:movies,id',
            'rating' => 'required|' . $commonRules['rating'],
        ], array_map(function($rule) {
            return 'nullable|' . $rule;
        }, array_diff_key($commonRules, ['rating' => null])));
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge([
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'movie_id.required' => 'The movie ID is required.',
            'movie_id.exists' => 'The selected movie does not exist.',
            'rating.required' => 'The rating is required.',
        ], $this->getCommonMessages());
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Always set current date for API-created reviews
        $this->merge([
            'review_date' => now()->toDateString(),
        ]);
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Ensure review_date is always set
        $validated['review_date'] = now()->toDateString();
        
        return $validated;
    }
}
