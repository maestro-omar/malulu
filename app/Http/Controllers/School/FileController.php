<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use Illuminate\Http\Request;
use App\Services\FileService;
use App\Services\CourseService;
use App\Models\Entities\School;
use App\Models\Catalogs\SchoolLevel;
use Inertia\Inertia;

use Diglactic\Breadcrumbs\Breadcrumbs;

class FileController extends SchoolBaseController
{
    protected $fileService;
    protected $courseService;

    public function __construct(CourseService $courseService, FileService $fileService)
    {
        $this->courseService = $courseService;
        $this->fileService = $fileService;
        parent::__construct();
    }

    public function create(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $subTypes = $this->fileService->getSubtypesForCourse($course);
        return Inertia::render('Files/byCourse/Create', [
            'subTypes' => $subTypes,
            'breadcrumbs' => Breadcrumbs::generate('school.course.file.create', $school, $schoolLevel, $course),
        ]);
    }
}
