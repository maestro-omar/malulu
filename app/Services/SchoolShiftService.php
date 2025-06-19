<?php

namespace App\Services;

use App\Models\SchoolShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SchoolShiftService
{
    /**
     * Get school shifts with filters
     */
    public function getSchoolShifts(Request $request = null, ?bool $active = null)
    {
        $query = SchoolShift::query();

        if ($request) {
            $query->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }

        if ($active !== null) {
            $query->where('active', $active);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Validate school shift data
     */
    public function validateSchoolShiftData(array $data, ?SchoolShift $schoolShift = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:school_shifts,name' . ($schoolShift ? ',' . $schoolShift->id : ''),
            'active' => 'boolean',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new school shift
     */
    public function createSchoolShift(array $data)
    {
        $validatedData = $this->validateSchoolShiftData($data);
        return SchoolShift::create($validatedData);
    }

    /**
     * Update an existing school shift
     */
    public function updateSchoolShift(SchoolShift $schoolShift, array $data)
    {
        $validatedData = $this->validateSchoolShiftData($data, $schoolShift);
        $schoolShift->update($validatedData);
        return $schoolShift;
    }

    /**
     * Delete a school shift
     */
    public function deleteSchoolShift(SchoolShift $schoolShift)
    {
        return $schoolShift->delete();
    }
} 