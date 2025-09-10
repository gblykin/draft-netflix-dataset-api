<?php

namespace App\Services\Filters;

use App\Helpers\BooleanHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseFilterService
{
    protected Builder $query;
    protected array $filters;

    public function __construct(Builder $query, array $filters = [])
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    /**
     * Convert various boolean representations to actual boolean value.
     * Supports: true, false, 1, 0, "true", "false", "1", "0"
     */
    protected function convertToBoolean($value): bool
    {
        return BooleanHelper::convertToBoolean($value);
    }

    public function apply(): LengthAwarePaginator
    {
        $this->applyFilters();
        $this->applySorting();
        
        return $this->applyPagination();
    }

    abstract protected function applyFilters(): void;
    abstract protected function getSortableFields(): array;

    protected function applySorting(): void
    {
        $sortBy = $this->filters['sort_by'] ?? $this->getDefaultSortField();
        $sortOrder = $this->filters['sort_order'] ?? 'asc';
        
        if (in_array($sortBy, $this->getSortableFields())) {
            $this->query->orderBy($sortBy, $sortOrder);
        }
    }

    protected function applyPagination(): LengthAwarePaginator
    {
        $perPage = min($this->filters['per_page'] ?? 15, 100);
        return $this->query->paginate($perPage);
    }

    abstract protected function getDefaultSortField(): string;
}