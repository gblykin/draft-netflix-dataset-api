<?php

namespace App\Services\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseFilterService
{
    protected Builder $query;
    protected Request $request;

    public function __construct(Builder $query, Request $request)
    {
        $this->query = $query;
        $this->request = $request;
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
        $sortBy = $this->request->input('sort_by', $this->getDefaultSortField());
        $sortOrder = $this->request->input('sort_order', 'asc');
        
        if (in_array($sortBy, $this->getSortableFields())) {
            $this->query->orderBy($sortBy, $sortOrder);
        }
    }

    protected function applyPagination(): LengthAwarePaginator
    {
        $perPage = min($this->request->input('per_page', 15), 100);
        return $this->query->paginate($perPage);
    }

    abstract protected function getDefaultSortField(): string;
}

