<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait WithTableReload
{
    public bool $isReloading = false;

    /**
     * Reload the table with empty state, delay, then data
     */
    public function reloadTable(): void
    {
        // First set reloading state to show empty table
        $this->isReloading = true;
        
        // Use JavaScript to handle the delay and then reload
        $this->js('
            setTimeout(() => {
                $wire.finishTableReload();
            }, 1);
        ');
    }

    /**
     * Complete the table reload after delay
     */
    public function finishTableReload(): void
    {
        $this->isReloading = false;
        $this->dispatch('finishReload');
    }

    /**
     * Get empty paginated results for loading state
     */
    protected function getEmptyPaginatedResults(): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(), // Empty collection
            0, // Total items
            $this->perPage ?? 10, // Items per page
            1, // Current page
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Get empty collection for loading state
     */
    protected function getEmptyCollection(): Collection
    {
        return collect();
    }

    /**
     * Wrapper method to handle reload state in computed properties
     */
    protected function handleReloadState($callback): mixed
    {
        if ($this->isReloading) {
            return $this->getEmptyPaginatedResults();
        }

        return $callback();
    }

    /**
     * Handle page updates to reload table data
     */
    public function updatedPage(): void
    {
        $this->reloadTable();
    }

    /**
     * Handle search updates to reset page and reload table
     */
    public function updatedSearch(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
        $this->reloadTable();
    }

    /**
     * Handle per page updates to reset page and reload table
     */
    public function updatedPerPage(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
        $this->reloadTable();
    }

    /**
     * Generic method to reset page and reload table for filter changes
     */
    public function resetPageAndReload(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
        $this->reloadTable();
    }

    /**
     * Update the listeners to include table reload
     */
    protected function getListeners(): array
    {
        return array_merge(
            $this->listeners ?? [],
            [
                'reloadTable' => 'reloadTable',
                'finishReload' => '$refresh',
            ]
        );
    }
}
