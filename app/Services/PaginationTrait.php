<?php

namespace App\Services;

trait PaginationTrait
{
    /**
     * Handle pagination logic based on per_page parameter
     */
    public function handlePagination($query, ?int $perPage, int $default = 10)
    {
        //limit pagination count
        if ($perPage === null) {
            // Default to 10 items per page
            return $query->paginate($default, ['*'], __('pagination.parameters.page'));
        } elseif ($perPage <= 0) {
            // Return all items without pagination
            return $query->get();
        } else {
            // Use the specified number of items per page
            return $query->paginate($perPage, ['*'], __('pagination.parameters.page'));
        }
    }
}
