<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

