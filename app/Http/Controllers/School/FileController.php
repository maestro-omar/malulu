<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use Illuminate\Http\Request;
use App\Services\FileService;
use App\Services\CourseService;
use App\Models\Entities\School;
use App\Models\Entities\File;
use App\Models\Catalogs\SchoolLevel;
use App\Traits\FileControllerTrait;
use Inertia\Inertia;

use Diglactic\Breadcrumbs\Breadcrumbs;

class FileController extends SchoolBaseController
{
    use FileControllerTrait;
    
    protected $courseService;

    public function __construct(CourseService $courseService, FileService $fileService)
    {
        $this->courseService = $courseService;
        $this->fileService = $fileService;
        parent::__construct();
    }

    public function createForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel']);
        $subTypes = $this->getSubtypesForContext('course', $course);
        $storeUrl = $this->getStoreUrlForContext('course', $course);
        $cancelUrl = $this->getCancelUrlForContext('course', $course);

        return Inertia::render('Files/byCourse/Create', [
            'subTypes' => $subTypes,
            'context' => 'course',
            'contextId' => $course->id,
            'storeUrl' => $storeUrl,
            'cancelUrl' => $cancelUrl,
            'breadcrumbs' => Breadcrumbs::generate('school.course.file.create', $school, $schoolLevel, $course),
        ]);
    }

    public function storeForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        return $this->storeFile($request, 'course', $course);
    }
    public function showForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel']);
        $file->load(['user']);
        return Inertia::render('Files/byCourse/Show', [
            'file' => $file,
            'course' => $course,
            'breadcrumbs' => Breadcrumbs::generate('school.course.file.show', $school, $schoolLevel, $course, $file),
        ]);
    }
    
    public function replaceForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel']);
        $subTypes = $this->getSubtypesForContext('course', $course);
        return Inertia::render('Files/byCourse/Replace', [
            'file' => $file,
            'subTypes' => $subTypes,
            'breadcrumbs' => Breadcrumbs::generate('school.course.file.replace', $school, $schoolLevel, $course, $file),
        ]);
    }
    
    public function editForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel']);
        $subTypes = $this->getSubtypesForContext('course', $course);
        return Inertia::render('Files/byCourse/Edit', [
            'file' => $file,
            'subTypes' => $subTypes,
            'breadcrumbs' => Breadcrumbs::generate('school.course.file.edit', $school, $schoolLevel, $course, $file),
        ]);
    }

    // School file management methods
    public function createForSchoolDirect(Request $request, School $school)
    {
        $subTypes = $this->getSubtypesForContext('school', $school);
        $storeUrl = $this->getStoreUrlForContext('school', $school);
        $cancelUrl = $this->getCancelUrlForContext('school', $school);

        return Inertia::render('Files/bySchool/Create', [
            'subTypes' => $subTypes,
            'context' => 'school',
            'contextId' => $school->id,
            'storeUrl' => $storeUrl,
            'cancelUrl' => $cancelUrl,
            'breadcrumbs' => Breadcrumbs::generate('school.file.create', $school),
        ]);
    }

    public function storeForSchoolDirect(Request $request, School $school)
    {
        return $this->storeFile($request, 'school', $school);
    }

    public function showForSchoolDirect(Request $request, School $school, File $file)
    {
        $file->load(['user']);
        return Inertia::render('Files/bySchool/Show', [
            'file' => $file,
            'breadcrumbs' => Breadcrumbs::generate('school.file.show', $school, $file),
        ]);
    }

    public function editForSchoolDirect(Request $request, School $school, File $file)
    {
        return Inertia::render('Files/bySchool/Edit', [
            'file' => $file,
            'breadcrumbs' => Breadcrumbs::generate('school.file.edit', $school, $file),
        ]);
    }

    public function replaceForSchoolDirect(Request $request, School $school, File $file)
    {
        return Inertia::render('Files/bySchool/Replace', [
            'file' => $file,
            'breadcrumbs' => Breadcrumbs::generate('school.file.replace', $school, $file),
        ]);
    }
}
