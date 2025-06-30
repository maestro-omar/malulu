<?php

namespace App\Services;

use App\Models\Entities\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Services\PaginationTrait;

class SchoolService
{
    use PaginationTrait;

    /**
     * Get schools with filters
     */
    public function getSchools(Request $request)
    {
        $query = School::query()
            ->with(['locality', 'locality.district', 'locality.district.province', 'schoolLevels', 'managementType', 'shifts'])
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
            ->when($request->input('province_code'), function ($query, $provinceCode) {
                $query->whereHas('locality.district.province', function ($query) use ($provinceCode) {
                    $query->where('code', $provinceCode);
                });
            })
            ->where('name', '!=', School::GLOBAL)
            ->orderBy('name');

        return $this->handlePagination($query, $request->input('per_page'));
    }

    /**
     * Validate school data
     */
    public function validateSchoolData(array $data, ?School $school = null)
    {
        // If slug is missing, generate from short
        if (empty($data['slug']) && !empty($data['short'])) {
            $data['slug'] = Str::slug($data['short']);
        }

        // Reserved words that cannot be used as slugs
        $reservedWords = ['sistema', 'escuela', 'mi-escuela', 'usuario', 'curso', 'provincia', 'general'];

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('schools', 'name')->ignore($school?->id)
            ],
            'short' => 'required|string|max:50',
            'slug' => [
                'required',
                'string',
                'max:50',
                Rule::unique('schools', 'slug')->ignore($school?->id),
                'regex:/^[a-z0-9-]+$/',
                function ($attribute, $value, $fail) use ($reservedWords) {
                    if (in_array($value, $reservedWords)) {
                        $fail('El slug no puede ser una palabra reservada: ' . implode(', ', $reservedWords));
                    }
                }
            ],
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
    public function getTrashedSchools(?Request $request = null)
    {
        $query = School::onlyTrashed()
            ->with(['locality', 'schoolLevels', 'managementType', 'shifts'])
            ->orderBy('name');

        if ($request) {
            return $this->handlePagination($query, $request->input('per_page'));
        }
        
        // Default behavior if no request provided
        return $query->paginate(10);
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

    /**
     * Get schools by province code
     */
    public function getSchoolsByProvince(string $provinceCode)
    {
        return School::query()
            ->with(['locality', 'locality.district', 'locality.district.province', 'schoolLevels', 'managementType', 'shifts'])
            ->whereHas('locality.district.province', function ($query) use ($provinceCode) {
                $query->where('code', $provinceCode);
            })
            ->where('name', '!=', School::GLOBAL)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get schools by district ID
     */
    public function getSchoolsByDistrict(int $districtId)
    {
        return School::query()
            ->with(['locality', 'locality.district', 'locality.district.province', 'schoolLevels', 'managementType', 'shifts'])
            ->whereHas('locality.district', function ($query) use ($districtId) {
                $query->where('id', $districtId);
            })
            ->where('name', '!=', School::GLOBAL)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get schools with advanced filters
     */
    public function getSchoolsWithAdvancedFilters(Request $request)
    {
        $query = School::query()
            ->with(['locality', 'locality.district', 'locality.district.province', 'schoolLevels', 'managementType', 'shifts'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('short', 'like', "%{$search}%")
                        ->orwhere('code', 'like', "%{$search}%")
                        ->orWhereHas('locality', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('locality.district', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('locality.district.province', function ($query) use ($search) {
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
            ->when($request->input('district_id'), function ($query, $districtId) {
                $query->whereHas('locality.district', function ($query) use ($districtId) {
                    $query->where('id', $districtId);
                });
            })
            ->when($request->input('province_code'), function ($query, $provinceCode) {
                $query->whereHas('locality.district.province', function ($query) use ($provinceCode) {
                    $query->where('code', $provinceCode);
                });
            })
            ->when($request->input('school_level_id'), function ($query, $schoolLevelId) {
                $query->whereHas('schoolLevels', function ($query) use ($schoolLevelId) {
                    $query->where('school_levels.id', $schoolLevelId);
                });
            })
            ->when($request->input('management_type_id'), function ($query, $managementTypeId) {
                $query->where('management_type_id', $managementTypeId);
            })
            ->where('name', '!=', School::GLOBAL)
            ->orderBy('name');

        return $this->handlePagination($query, $request->input('per_page'));
    }

    // Add other business logic methods...
}