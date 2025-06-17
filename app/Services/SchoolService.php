<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SchoolService
{
    /**
     * Get schools with filters
     */
    public function getSchools(Request $request)
    {
        $query = School::query()
            ->with(['locality', 'schoolLevels', 'managementType', 'shifts'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('short', 'like', "%{$search}%")
                        ->orwhere('code', 'like', "%{$search}%")
                        ->orWhereHas('locality', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('schoolLevels', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('locality_id'), function ($query, $localityId) {
                $query->where('locality_id', $localityId);
            })
            ->where('name', '!=', School::GLOBAL);

        return $query->orderBy('name')->paginate(10);
    }

    /**
     * Validate school data
     */
    public function validateSchoolData(array $data, ?School $school = null)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('schools', 'name')->ignore($school?->id)
            ],
            'short' => 'required|string|max:50',
            'cue' => [
                'required',
                'string',
                'max:50',
                Rule::unique('schools', 'cue')->ignore($school?->id)
            ],
            'locality_id' => [
                'required',
                Rule::exists('localities', 'id')
            ],
            'management_type_id' => 'required|exists:school_management_types,id',
            'school_levels' => 'required|array',
            'school_levels.*' => 'exists:school_levels,id',
            'shifts' => 'nullable|array',
            'shifts.*' => 'exists:school_shifts,id',
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'coordinates' => 'nullable|string|max:100',
            'social' => 'nullable|array',
            'logo' => 'nullable|image|max:1024', // max 1MB
            'picture' => 'nullable|image|max:2048' // max 2MB
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new school
     */
    public function createSchool(array $data)
    {
        $validatedData = $this->validateSchoolData($data);

        $school = School::create($validatedData);

        if (isset($validatedData['school_levels'])) {
            $school->schoolLevels()->sync($validatedData['school_levels']);
        }

        if (isset($validatedData['shifts'])) {
            $school->shifts()->sync($validatedData['shifts']);
        }

        return $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']);
    }

    /**
     * Update an existing school
     */
    public function updateSchool(School $school, array $data)
    {
        if ($school->name === School::GLOBAL) {
            throw new \Exception('Cannot update GLOBAL school.');
        }

        $validatedData = $this->validateSchoolData($data, $school);

        $school->update($validatedData);

        if (isset($validatedData['school_levels'])) {
            $school->schoolLevels()->sync($validatedData['school_levels']);
        }

        if (isset($validatedData['shifts'])) {
            $school->shifts()->sync($validatedData['shifts']);
        } else {
            $school->shifts()->detach();
        }

        return $school->load(['locality', 'schoolLevels', 'managementType', 'shifts']);
    }

    /**
     * Delete a school
     */
    public function deleteSchool(School $school)
    {
        if ($school->name === School::GLOBAL) {
            throw new \Exception('Cannot delete GLOBAL school.');
        }

        return $school->delete();
    }

    /**
     * Get trashed schools
     */
    public function getTrashedSchools()
    {
        return School::onlyTrashed()
            ->with(['locality', 'schoolLevels', 'managementType', 'shifts'])
            ->orderBy('name')
            ->paginate(10);
    }

    /**
     * Restore a trashed school
     */
    public function restoreSchool($id)
    {
        $school = School::onlyTrashed()->findOrFail($id);
        return $school->restore();
    }

    /**
     * Permanently delete a school
     */
    public function forceDeleteSchool($id)
    {
        $school = School::onlyTrashed()->findOrFail($id);
        return $school->forceDelete();
    }

    // Add other business logic methods...
}