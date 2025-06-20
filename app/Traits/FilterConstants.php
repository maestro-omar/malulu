<?php

namespace App\Traits;

trait FilterConstants
{
    /**
     * Get class constants excluding common Laravel model constants.
     *
     * @param array $exclude Optional: additional constants to exclude.
     * @return array
     */
    public static function getFilteredConstants(array $exclude = []): array
    {
        $reflectionClass = new \ReflectionClass(static::class);
        $allConstants = $reflectionClass->getConstants();

        $laravelModelConstants = [
            'CREATED_AT',
            'UPDATED_AT',
            'DELETED_AT',
            'ID',
            'FOREIGN_KEY_SUFFIX',
            // Add any other common Laravel constants you want to exclude by default
        ];

        $filteredConstants = array_filter($allConstants, function ($key) use ($laravelModelConstants, $exclude) {
            return !in_array($key, $laravelModelConstants) && !in_array($key, $exclude);
        }, ARRAY_FILTER_USE_KEY);

        return $filteredConstants;
    }
} 