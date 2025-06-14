<?php

namespace App\Services;

use App\Models\ClassSubject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ClassSubjectService
{
    /**
     * Get all class subjects with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return ClassSubject::with('schoolLevel')->paginate($perPage);
    }

    /**
     * Get all class subjects
     */
    public function getAll(): Collection
    {
        return ClassSubject::with('schoolLevel')->get();
    }

    /**
     * Create a new class subject
     */
    public function create(array $data): ClassSubject
    {
        return ClassSubject::create($data);
    }

    /**
     * Update a class subject
     */
    public function update(ClassSubject $classSubject, array $data): bool
    {
        return $classSubject->update($data);
    }

    /**
     * Delete a class subject
     */
    public function delete(ClassSubject $classSubject): bool
    {
        return $classSubject->delete();
    }

    /**
     * Find a class subject by ID
     */
    public function find(int $id): ?ClassSubject
    {
        return ClassSubject::with('schoolLevel')->find($id);
    }
} 