<?php

namespace App\Services;

trait PaginationTrait
{
    /**
     * Handle pagination logic based on per_page parameter
     */
    public function handlePagination($query, $perPage)
    {
        if ($perPage === null) {
            // Default to 10 items per page
            return $query->paginate(10);
        } elseif ($perPage <= 0) {
            // Return all items without pagination
            return $query->get();
        } else {
            // Use the specified number of items per page
            return $query->paginate($perPage);
        }
    }
} 