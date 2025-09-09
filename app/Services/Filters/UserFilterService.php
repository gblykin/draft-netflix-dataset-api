<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilterService extends BaseFilterService
{
    protected function applyFilters(): void
    {
        if (isset($this->filters['subscription_plan'])) {
            $this->query->where('subscription_plan', $this->filters['subscription_plan']);
        }

        if (isset($this->filters['country'])) {
            $this->query->where('country', 'like', '%' . $this->filters['country'] . '%');
        }

        if (isset($this->filters['gender'])) {
            $this->query->where('gender', $this->filters['gender']);
        }

        if (isset($this->filters['age_min'])) {
            $this->query->where('age', '>=', $this->filters['age_min']);
        }

        if (isset($this->filters['age_max'])) {
            $this->query->where('age', '<=', $this->filters['age_max']);
        }

        if (isset($this->filters['is_active'])) {
            $this->query->where('is_active', (bool) $this->filters['is_active']);
        }

        if (isset($this->filters['primary_device'])) {
            $this->query->where('primary_device', 'like', '%' . $this->filters['primary_device'] . '%');
        }

        if (isset($this->filters['household_size_min'])) {
            $this->query->where('household_size', '>=', $this->filters['household_size_min']);
        }

        if (isset($this->filters['household_size_max'])) {
            $this->query->where('household_size', '<=', $this->filters['household_size_max']);
        }
    }

    protected function getSortableFields(): array
    {
        return ['external_user_id', 'age', 'subscription_start_date', 'monthly_spend', 'household_size'];
    }

    protected function getDefaultSortField(): string
    {
        return 'external_user_id';
    }
}
