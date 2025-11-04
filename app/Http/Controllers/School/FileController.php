<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\School\SchoolBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FileService;
use App\Services\CourseService;
use App\Models\Entities\School;
use App\Models\Entities\File;
use App\Models\Entities\User;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\FileType;
use App\Traits\FileControllerTrait;
use Inertia\Inertia;
use Illuminate\Support\Str;

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

    public function createForCourse(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
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

    public function storeForCourse(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        return $this->storeFile($request, 'course', $course);
    }
    public function showForCourse(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
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


    public function editForCourse(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        $course->load(['school', 'schoolLevel']);
        $subTypes = $this->getSubtypesForContext('course', $course);
        return Inertia::render('Files/byCourse/Edit', [
            'file' => $file,
            'course' => $course,
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
            'school' => $school,
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
        $subTypes = $this->getSubtypesForContext('school', $school);
        return Inertia::render('Files/bySchool/Edit', [
            'file' => $file,
            'school' => $school,
            'subTypes' => $subTypes,
            'breadcrumbs' => Breadcrumbs::generate('school.file.edit', $school, $file),
        ]);
    }

    public function replaceForSchoolDirect(Request $request, School $school, File $file)
    {
        if ($request->isMethod('get')) {
            $subTypes = $this->getSubtypesForContext('school', $school);
            return Inertia::render('Files/bySchool/Replace', [
                'file' => $file,
                'school' => $school,
                'subTypes' => $subTypes,
                'breadcrumbs' => Breadcrumbs::generate('school.file.replace', $school, $file),
            ]);
        }

        return $this->replaceFile($request, $file, 'school', $school);
    }

    public function updateForSchoolDirect(Request $request, School $school, File $file)
    {
        return $this->updateFile($request, $file, 'school', $school);
    }

    public function updateForCourse(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        return $this->updateFile($request, $file, 'course', $course);
    }

    public function replaceForCourse(Request $request, School $school, SchoolLevel $schoolLevel, string $courseIdAndLabel, File $file)
    {
        if ($request->isMethod('get')) {
            $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
            $course->load(['school', 'schoolLevel']);
            $subTypes = $this->getSubtypesForContext('course', $course);
            return Inertia::render('Files/byCourse/Replace', [
                'file' => $file,
                'course' => $course,
                'subTypes' => $subTypes,
                'breadcrumbs' => Breadcrumbs::generate('school.course.file.replace', $school, $schoolLevel, $course, $file),
            ]);
        }

        $course = $this->getCourseFromUrlParameter($courseIdAndLabel);
        return $this->replaceFile($request, $file, 'course', $course);
    }

    // School User Files Management
    public function createForStudent(Request $request, School $school, User $user)
    {
        $subTypes = $this->getSubtypesForContext(['user', 'student'], $user);
        $breadCrumbs = Breadcrumbs::generate('school.student.file.create', $school, $user);
        $cancelUrl = route('school.student.show', [
            'school' => $school->slug,
            'idAndName' => $user->id . '-' . Str::slug($user->name . ' ' . $user->lastname)
        ]);
        $storeUrl = route('school.student.file.store', [
            'school' => $school->slug,
            'user' => $user->id
        ]);
        return Inertia::render('Files/byStudent/Create', [
            'subTypes' => $subTypes,
            'context' => 'user',
            'contextId' => $user->id,
            'storeUrl' => $storeUrl,
            'cancelUrl' => $cancelUrl,
            'breadcrumbs' => $breadCrumbs,
        ]);
    }

    public function storeForStudent(Request $request, School $school, User $user)
    {
        try {
            $validated = $request->validate([
                'subtype_id' => 'required|exists:file_subtypes,id',
                'nice_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'context' => 'required|string|in:user,school,course,province',
                'context_id' => 'required',
                'file' => 'nullable|file|max:10240', // 10MB max, nullable for external URLs
                'external_url' => 'nullable|url|max:500', // nullable for file uploads
                'valid_from' => 'nullable|date',
                'valid_until' => 'nullable|date|after_or_equal:valid_from'
            ]);

            // Custom validation: ensure either file or external_url is provided
            if (empty($validated['file']) && empty($validated['external_url'])) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    ['file' => ['Debe proporcionar un archivo o una URL externa.']]
                );
            }

            $fileService = $this->getFileService();

            // Prepare file data with required fields
            $fileData = [
                'subtype_id' => $validated['subtype_id'],
                'user_id' => Auth::id(), // User who created the file
                'nice_name' => $validated['nice_name'],
                'description' => $validated['description'] ?? '',
                'active' => true,
                'metadata' => [],
                'target_user_id' => $user->id // User the file belongs to
            ];

            // Handle file upload or external URL
            if (!empty($validated['file']) && $request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $this->handleFileUpload($fileData, $uploadedFile, 'user', $user);
            } elseif (!empty($validated['external_url'])) {
                $this->handleExternalUrl($fileData, $validated['external_url']);
            }

            // Handle expiration dates if provided
            if (isset($validated['valid_from']) || isset($validated['valid_until'])) {
                $fileData['valid_from'] = $validated['valid_from'] ?? null;
                $fileData['valid_until'] = $validated['valid_until'] ?? null;
            }

            // Create the file record
            $file = $fileService->createFile($fileData);

            // Use school context redirect
            return redirect()->route('school.student.file.show', [
                'school' => $school->slug,
                'user' => $user->id,
                'file' => $file->id
            ])->with('success', 'Archivo creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('school.student.file.create', [
                'school' => $school->slug,
                'user' => $user->id
            ])->withErrors(['error' => 'Error al crear el archivo: ' . $e->getMessage()])->withInput();
        }
    }

    public function showForStudent(Request $request, School $school, User $user, File $file)
    {
        $loggedUser = auth()->user();
        $fileData = $this->fileService->getFileDataForUser($file, $loggedUser, $user);
        $history = $fileData['history'];
        return Inertia::render('Files/byStudent/Show', [
            'file' => $fileData['file'],
            'user' => $user,
            'school' => $school,
            'history' => $history,
            'breadcrumbs' => Breadcrumbs::generate('school.student.file.show', $school, $user, $file),
        ]);
    }

    public function editForStudent(Request $request, School $school, User $user, File $file)
    {
        $subTypes = $this->getSubtypesForContext('user', $user);
        return Inertia::render('Files/byStudent/Edit', [
            'file' => $file,
            'user' => $user,
            'school' => $school,
            'subTypes' => $subTypes,
            'breadcrumbs' => Breadcrumbs::generate('school.student.file.edit', $school, $user, $file),
        ]);
    }

    public function updateForStudent(Request $request, School $school, User $user, File $file)
    {
        try {
            $data = $request->only(['subtype_id', 'nice_name', 'description', 'valid_from', 'valid_until']);
            $this->fileService->updateFileMetadata($file, $data);

            return redirect()->route('school.student.file.show', [
                'school' => $school->slug,
                'user' => $user->id,
                'file' => $file->id
            ])->with('success', 'Archivo actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('school.student.file.edit', [
                'school' => $school->slug,
                'user' => $user->id,
                'file' => $file->id
            ])->withErrors(['error' => 'Error al actualizar el archivo: ' . $e->getMessage()])->withInput();
        }
    }

    public function replaceForStudent(Request $request, School $school, User $user, File $file)
    {
        if ($request->isMethod('get')) {
            $subTypes = $this->getSubtypesForContext('user', $user);
            return Inertia::render('Files/byStudent/Replace', [
                'file' => $file,
                'user' => $user,
                'school' => $school,
                'subTypes' => $subTypes,
                'breadcrumbs' => Breadcrumbs::generate('school.student.file.replace', $school, $user, $file),
            ]);
        }

        try {
            $data = $request->only(['subtype_id', 'nice_name', 'description']);
            $uploadedFile = $request->hasFile('file') ? $request->file('file') : null;
            $externalUrl = $request->input('external_url');

            $newFile = $this->fileService->replaceUserFile(
                $user,
                $file,
                $data,
                $uploadedFile,
                $externalUrl,
                Auth::id()
            );

            return redirect()->route('school.student.file.show', [
                'school' => $school->slug,
                'user' => $user->id,
                'file' => $newFile->id
            ])->with('success', 'Archivo reemplazado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('school.student.file.replace', [
                'school' => $school->slug,
                'user' => $user->id,
                'file' => $file->id
            ])->withErrors(['error' => 'Error al reemplazar el archivo: ' . $e->getMessage()])->withInput();
        }
    }
}
