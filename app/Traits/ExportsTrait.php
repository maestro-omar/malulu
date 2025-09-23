<?php

namespace App\Traits;

trait ExportsTrait
{
    /**
     * Format birthdate from Y-m-d to d/m/Y format
     *
     * @param string|null $birthdate
     * @return string|null
     */
    protected function formatBirthdate(?string $birthdate): ?string
    {
        if (empty($birthdate)) {
            return null;
        }

        try {
            $date = \DateTime::createFromFormat('Y-m-d', $birthdate);
            if ($date === false) {
                // If the format doesn't match, return the original value
                return $birthdate;
            }
            return $date->format('d/m/Y');
        } catch (\Exception $e) {
            // If any error occurs, return the original value
            return $birthdate;
        }
    }
}
