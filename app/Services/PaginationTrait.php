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

    public function decodeLengthAwarePaginator($response)
    {
        // If it's not a LengthAwarePaginator, return as is
        if (!$response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return $response;
        }

        // Manually extract pagination data to avoid json_encode/json_decode issues
        return [
            'current_page' => $response->currentPage(),
            'data' => $response->items(), // This will return the collection items
            'first_page_url' => $response->url(1),
            'from' => $response->firstItem(),
            'last_page' => $response->lastPage(),
            'last_page_url' => $response->url($response->lastPage()),
            'links' => $response->linkCollection()->toArray(),
            'next_page_url' => $response->nextPageUrl(),
            'path' => $response->path(),
            'per_page' => $response->perPage(),
            'prev_page_url' => $response->previousPageUrl(),
            'to' => $response->lastItem(),
            'total' => $response->total(),
        ];
    }
}
