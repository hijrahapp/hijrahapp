<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait WithTableSorting
{
    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function setSort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Apply sorting to a query builder instance.
     */
    public function applySorting(Builder $query): Builder
    {
        return $query->orderBy($this->sortBy, $this->sortDirection);
    }
}
