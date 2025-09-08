<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilterService extends BaseFilterService
{
    protected function applyFilters(): void
    {
        if ($this->request->has('subscription_plan')) {
            $this->query->where('subscription_plan', $this->request->input('subscription_plan'));
        }

        if ($this->request->has('country')) {
            $this->query->where('country', 'like', '%' . $this->request->input('country') . '%');
        }

        if ($this->request->has('gender')) {
            $this->query->where('gender', $this->request->input('gender'));
        }

        if ($this->request->has('age_min')) {
            $this->query->where('age', '>=', $this->request->input('age_min'));
        }

        if ($this->request->has('age_max')) {
            $this->query->where('age', '<=', $this->request->input('age_max'));
        }

        if ($this->request->has('is_active')) {
            $this->query->where('is_active', $this->request->boolean('is_active'));
        }

        if ($this->request->has('primary_device')) {
            $this->query->where('primary_device', 'like', '%' . $this->request->input('primary_device') . '%');
        }

        if ($this->request->has('household_size_min')) {
            $this->query->where('household_size', '>=', $this->request->input('household_size_min'));
        }

        if ($this->request->has('household_size_max')) {
            $this->query->where('household_size', '<=', $this->request->input('household_size_max'));
        }
    }

    protected function getSortableFields(): array
    {
        return ['user_id', 'age', 'subscription_start_date', 'monthly_spend', 'household_size'];
    }

    protected function getDefaultSortField(): string
    {
        return 'user_id';
    }
}
