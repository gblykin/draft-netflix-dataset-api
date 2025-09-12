<?php

namespace App\Http\Requests;

use App\Enums\Device;
use App\Enums\Sentiment;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseReviewRequest extends FormRequest
{
    /**
     * Get common validation rules for reviews.
     */
    protected function getCommonRules(): array
    {
        return [
            'rating' => 'integer|min:1|max:5',
            'review_text' => 'nullable|string|max:2000',
            'device_type' => 'string|in:' . implode(',', Device::values()),
            'is_verified_watch' => 'boolean',
            'helpful_votes' => 'integer|min:0',
            'total_votes' => 'integer|min:0',
            'sentiment' => 'string|in:' . implode(',', Sentiment::values()),
            'sentiment_score' => 'numeric|min:-1|max:1',
        ];
    }

    /**
     * Get common error messages for reviews.
     */
    protected function getCommonMessages(): array
    {
        return [
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating must not be greater than 5.',
            'review_text.max' => 'The review text may not be greater than 2000 characters.',
            'device_type.in' => 'The device type must be one of: ' . implode(', ', Device::values()) . '.',
            'helpful_votes.min' => 'The helpful votes must be at least 0.',
            'total_votes.min' => 'The total votes must be at least 0.',
            'sentiment.in' => 'The sentiment must be one of: ' . implode(', ', Sentiment::values()) . '.',
            'sentiment_score.min' => 'The sentiment score must be at least -1.',
            'sentiment_score.max' => 'The sentiment score must not be greater than 1.',
        ];
    }
}
