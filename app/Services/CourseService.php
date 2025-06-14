<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourseService
{
    /**
     * Get courses with filters
     */
    public function getCourses(Request $request)
    {
        $query = Course::query()
            ->with(['school', 'schoolLevel', 'schoolShift', 'previousCourse'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhere('letter', 'like', "%{$search}%")
                        ->orWhereHas('school', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('schoolLevel', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('schoolShift', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->input('school_id'), function ($query, $schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($request->input('school_level_id'), function ($query, $schoolLevelId) {
                $query->where('school_level_id', $schoolLevelId);
            })
            ->when($request->input('school_shift_id'), function ($query, $schoolShiftId) {
                $query->where('school_shift_id', $schoolShiftId);
            })
            ->when($request->input('active') !== null, function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            });

        return $query->orderBy('number')->orderBy('letter')->paginate(10);
    }

    /**
     * Validate course data
     */
    public function validateCourseData(array $data, ?Course $course = null)
    {
        $rules = [
            'school_id' => [
                'required',
                Rule::exists('schools', 'id')
            ],
            'school_level_id' => [
                'required',
                Rule::exists('school_levels', 'id')
            ],
            'school_shift_id' => [
                'required',
                Rule::exists('school_shifts', 'id')
            ],
            'previous_course_id' => [
                'nullable',
                Rule::exists('courses', 'id')
            ],
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:9'
            ],
            'letter' => [
                'required',
                'string',
                'size:1',
                'regex:/^[A-Z]$/'
            ],
            'start_date' => [
                'required',
                'date'
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date'
            ],
            'active' => [
                'boolean'
            ]
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new course
     */
    public function createCourse(array $data)
    {
        $validatedData = $this->validateCourseData($data);
        return Course::create($validatedData);
    }

    /**
     * Update an existing course
     */
    public function updateCourse(Course $course, array $data)
    {
        $validatedData = $this->validateCourseData($data, $course);
        $course->update($validatedData);
        return $course;
    }

    /**
     * Delete a course
     */
    public function deleteCourse(Course $course)
    {
        return $course->delete();
    }

    /**
     * Get active courses for a school
     */
    public function getActiveCoursesForSchool($schoolId)
    {
        return Course::where('school_id', $schoolId)
            ->where('active', true)
            ->with(['schoolLevel', 'schoolShift'])
            ->orderBy('number')
            ->orderBy('letter')
            ->get();
    }

    /**
     * Get courses by school level
     */
    public function getCoursesBySchoolLevel($schoolLevelId)
    {
        return Course::where('school_level_id', $schoolLevelId)
            ->where('active', true)
            ->with(['school', 'schoolShift'])
            ->orderBy('number')
            ->orderBy('letter')
            ->get();
    }

    /**
     * Get courses by school shift
     */
    public function getCoursesBySchoolShift($schoolShiftId)
    {
        return Course::where('school_shift_id', $schoolShiftId)
            ->where('active', true)
            ->with(['school', 'schoolLevel'])
            ->orderBy('number')
            ->orderBy('letter')
            ->get();
    }
} 