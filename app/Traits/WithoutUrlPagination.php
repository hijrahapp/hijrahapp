<?php

namespace App\Traits;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;

trait WithoutUrlPagination
{
    public $paginators = [];
    protected $paginationTheme = 'tailwind';

    // Custom pagination methods that don't update URL
    public function gotoPage($page, $pageName = 'page')
    {
        $this->setPage($page, $pageName);
    }

    public function nextPage($pageName = 'page')
    {
        $this->setPage(($this->paginators[$pageName] ?? 1) + 1, $pageName);
    }

    public function previousPage($pageName = 'page')
    {
        $this->setPage(max(($this->paginators[$pageName] ?? 1) - 1, 1), $pageName);
    }

    public function getPage($pageName = 'page')
    {
        return $this->paginators[$pageName] ?? 1;
    }

    public function setPage($page, $pageName = 'page')
    {
        if (is_numeric($page)) {
            $page = (int) ($page <= 0 ? 1 : $page);
        }

        $beforePaginatorMethod = 'updatingPaginators';
        $afterPaginatorMethod = 'updatedPaginators';

        $beforeMethod = 'updating' . ucfirst(Str::camel($pageName));
        $afterMethod = 'updated' . ucfirst(Str::camel($pageName));

        if (method_exists($this, $beforePaginatorMethod)) {
            $this->{$beforePaginatorMethod}($page, $pageName);
        }

        if (method_exists($this, $beforeMethod)) {
            $this->{$beforeMethod}($page, null);
        }

        $this->paginators[$pageName] = $page;

        if (method_exists($this, $afterPaginatorMethod)) {
            $this->{$afterPaginatorMethod}($page, $pageName);
        }

        if (method_exists($this, $afterMethod)) {
            $this->{$afterMethod}($page, null);
        }
    }

    // Disable pagination URL caching
    public function paginationView()
    {
        return 'shared.components.ktui-pagination';
    }

    // Override to completely disable pagination URL caching
    public function queryStringHandlesPagination()
    {
        return []; // Return empty array to disable URL caching for pagination
    }

    // Override to prevent any query string updates
    protected function getQueryString()
    {
        $queryString = [];

        // Only include non-pagination properties
        $properties = $this->getPublicPropertiesDefinedBySubClass();

        foreach ($properties as $property) {
            if (!in_array($property, ['page', 'paginators'])) {
                $queryString[$property] = ['history' => true, 'keep' => false];
            }
        }

        return $queryString;
    }

    // Override to prevent URL updates for pagination properties
    public function updated($property)
    {
        // Don't update URL for pagination-related properties
        if (in_array($property, ['page', 'paginators'])) {
            return;
        }

        // Only call parent if it exists
        if (method_exists(get_parent_class($this), 'updated')) {
            parent::updated($property);
        }
    }
}
