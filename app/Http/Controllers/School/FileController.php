<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use Illuminate\Http\Request;
use App\Services\FileService;
use App\Services\CourseService;
use App\Models\Entities\School;
use App\Models\Entities\File;
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

    public function createForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $subTypes = $this->fileService->getSubtypesForCourse($course);
        dd($subTypes);
        return Inertia::render('Files/byCourse/Create', [
            'subTypes' => $subTypes,
            'breadcrumbs' => Breadcrumbs::generate('school.course.file.create', $school, $schoolLevel, $course),
        ]);
    }
    public function showForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
       dd($file,'show');
    }
    public function replaceForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
       dd($file,'replace');
    }
    public function editForSchool(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
        dd($file,'edit');
    }
}
