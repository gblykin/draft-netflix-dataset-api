<?php

namespace App\Http\Requests;

use App\Enums\Sentiment;
use Illuminate\Foundation\Http\FormRequest;

class ReviewListRequest extends FormRequest
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
            'sort_by' => 'sometimes|string|in:review_date,rating,helpful_votes,total_votes,sentiment_score',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            
            // Filtering
            'user_id' => 'sometimes|integer|exists:users,id',
            'movie_id' => 'sometimes|integer|exists:movies,id',
            'rating' => 'sometimes|integer|min:1|max:5',
            'rating_min' => 'sometimes|integer|min:1|max:5',
            'rating_max' => 'sometimes|integer|min:1|max:5|gte:rating_min',
            'device_type' => 'sometimes|string|max:255',
            'is_verified_watch' => 'sometimes|in:true,false,1,0',
            'sentiment' => 'sometimes|string|in:' . implode(',', Sentiment::values()),
            'sentiment_score_min' => 'sometimes|numeric|min:-1|max:1',
            'sentiment_score_max' => 'sometimes|numeric|min:-1|max:1|gte:sentiment_score_min',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
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
            'sort_by.in' => 'The sort by field must be one of: review_date, rating, helpful_votes, total_votes, sentiment_score.',
            'sort_direction.in' => 'The sort direction must be either asc or desc.',
            'user_id.exists' => 'The selected user does not exist.',
            'movie_id.exists' => 'The selected movie does not exist.',
            'rating.in' => 'The rating must be between 1 and 5.',
            'rating_min.in' => 'The minimum rating must be between 1 and 5.',
            'rating_max.in' => 'The maximum rating must be between 1 and 5.',
            'rating_max.gte' => 'The maximum rating must be greater than or equal to the minimum rating.',
            'sentiment.in' => 'The sentiment must be one of: ' . implode(', ', Sentiment::values()) . '.',
            'sentiment_score_min.min' => 'The minimum sentiment score must be at least -1.',
            'sentiment_score_min.max' => 'The minimum sentiment score must be at most 1.',
            'sentiment_score_max.min' => 'The maximum sentiment score must be at least -1.',
            'sentiment_score_max.max' => 'The maximum sentiment score must be at most 1.',
            'sentiment_score_max.gte' => 'The maximum sentiment score must be greater than or equal to the minimum sentiment score.',
            'date_to.after_or_equal' => 'The date to must be after or equal to the date from.',
        ];
    }
}
