<?php

namespace App\Services;

use App\Models\Catalogs\SchoolLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SchoolLevelService
{
    /**
     * Get school levels with filters
     */
    public function getSchoolLevels(Request $request = null)
    {
        $query = SchoolLevel::query();

        if ($request) {
            $query->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Validate school level data
     */
    public function validateSchoolLevelData(array $data, ?SchoolLevel $schoolLevel = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:school_levels,name' . ($schoolLevel ? ',' . $schoolLevel->id : ''),
            'active' => 'boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new school level
     */
    public function createSchoolLevel(array $data)
    {
        $validatedData = $this->validateSchoolLevelData($data);
        return SchoolLevel::create($validatedData);
    }

    /**
     * Update an existing school level
     */
    public function updateSchoolLevel(SchoolLevel $schoolLevel, array $data)
    {
        $validatedData = $this->validateSchoolLevelData($data, $schoolLevel);
        $schoolLevel->update($validatedData);
        return $schoolLevel;
    }

    /**
     * Delete a school level
     */
    public function deleteSchoolLevel(SchoolLevel $schoolLevel)
    {
        return $schoolLevel->delete();
    }
}
