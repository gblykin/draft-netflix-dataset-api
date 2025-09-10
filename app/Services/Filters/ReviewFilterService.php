<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewFilterService extends BaseFilterService
{
    protected function applyFilters(): void
    {
        if (isset($this->filters['user_id'])) {
            $this->query->where('user_id', $this->filters['user_id']);
        }

        if (isset($this->filters['movie_id'])) {
            $this->query->where('movie_id', $this->filters['movie_id']);
        }

        if (isset($this->filters['rating'])) {
            $this->query->where('rating', $this->filters['rating']);
        }

        if (isset($this->filters['rating_min'])) {
            $this->query->where('rating', '>=', $this->filters['rating_min']);
        }

        if (isset($this->filters['rating_max'])) {
            $this->query->where('rating', '<=', $this->filters['rating_max']);
        }

        if (isset($this->filters['device_type'])) {
            $this->query->where('device_type', 'like', '%' . $this->filters['device_type'] . '%');
        }

        if (isset($this->filters['is_verified_watch'])) {
            $value = $this->filters['is_verified_watch'];
            $booleanValue = $this->convertToBoolean($value);
            $this->query->where('is_verified_watch', $booleanValue);
        }

        if (isset($this->filters['sentiment'])) {
            $this->query->where('sentiment', $this->filters['sentiment']);
        }

        if (isset($this->filters['sentiment_score_min'])) {
            $this->query->where('sentiment_score', '>=', $this->filters['sentiment_score_min']);
        }

        if (isset($this->filters['sentiment_score_max'])) {
            $this->query->where('sentiment_score', '<=', $this->filters['sentiment_score_max']);
        }

        if (isset($this->filters['date_from'])) {
            $this->query->where('review_date', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $this->query->where('review_date', '<=', $this->filters['date_to']);
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
