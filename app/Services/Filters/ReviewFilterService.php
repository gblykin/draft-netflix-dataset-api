<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewFilterService extends BaseFilterService
{
    protected function applyFilters(): void
    {
        if ($this->request->has('user_id')) {
            $this->query->where('user_id', $this->request->input('user_id'));
        }

        if ($this->request->has('movie_id')) {
            $this->query->where('movie_id', $this->request->input('movie_id'));
        }

        if ($this->request->has('rating')) {
            $this->query->where('rating', $this->request->input('rating'));
        }

        if ($this->request->has('rating_min')) {
            $this->query->where('rating', '>=', $this->request->input('rating_min'));
        }

        if ($this->request->has('rating_max')) {
            $this->query->where('rating', '<=', $this->request->input('rating_max'));
        }

        if ($this->request->has('device_type')) {
            $this->query->where('device_type', 'like', '%' . $this->request->input('device_type') . '%');
        }

        if ($this->request->has('is_verified_watch')) {
            $this->query->where('is_verified_watch', $this->request->boolean('is_verified_watch'));
        }

        if ($this->request->has('sentiment')) {
            $this->query->where('sentiment', $this->request->input('sentiment'));
        }

        if ($this->request->has('sentiment_score_min')) {
            $this->query->where('sentiment_score', '>=', $this->request->input('sentiment_score_min'));
        }

        if ($this->request->has('sentiment_score_max')) {
            $this->query->where('sentiment_score', '<=', $this->request->input('sentiment_score_max'));
        }

        if ($this->request->has('date_from')) {
            $this->query->where('review_date', '>=', $this->request->input('date_from'));
        }

        if ($this->request->has('date_to')) {
            $this->query->where('review_date', '<=', $this->request->input('date_to'));
        }
    }

    protected function getSortableFields(): array
    {
        return ['review_date', 'rating', 'helpful_votes', 'total_votes', 'sentiment_score'];
    }

    protected function getDefaultSortField(): string
    {
        return 'review_date';
    }
}
