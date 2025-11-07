<?php

namespace App\Http\Controllers\School;

use App\Models\Entities\User;
use App\Models\Entities\School;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\Country;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;
use App\Services\FileService;
use App\Services\DiagnosisService;
use App\Models\Catalogs\Diagnosis;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Validation\ValidationException;

class UserController extends SchoolBaseController
{
    protected $userService;
    protected $fileService;
    protected ?School $school;

    public function __construct(UserService $userService, FileService $fileService)
    {
        $this->userService = $userService;
        $this->fileService = $fileService;
        $this->middleware('permission:view users')->only(['index', 'trashed']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy', 'restore', 'forceDelete']);
    }

    public function staff(Request $request, $slug): Response
    {
        try {
            $this->setSchool($slug);

            // Load school with shifts relationship
            $this->school->load('shifts');

            // Transform school shifts to match the expected format
            $school = $this->school->toArray();
            $school['shifts'] = $this->school->shifts ? $this->school->shifts->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'label' => $shift->name, // Use name as label for consistency
                    'code' => $shift->code
                ];
            })->toArray() : [];

            return $this->render($request, 'Users/BySchool/Staff', [
                'users' => $this->userService->getStaffBySchool($request, $this->school->id),
                'school' => $school,
                'filters' => $request->only(['search', 'sort', 'direction', 'shift', 'roles']),
                'breadcrumbs' => Breadcrumbs::generate('schools.staff', $this->school),
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Staff page error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception to see the error
            throw $e;
        }
    }

    public function students(Request $request, $slug): Response
    {
        $this->setSchool($slug);

        $students = $this->userService->getStudentsBySchool($request, $this->school->id);
        return $this->render($request, 'Users/BySchool/Students', [
            'users' => $students,
            'school' => $this->school,
            'filters' => $request->only(['search', 'sort', 'direction']),
            'breadcrumbs' => Breadcrumbs::generate('schools.students', $this->school),
        ]);
    }

    public function student(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $data = $this->getUserData($studentIdAndName);
        $student = $data ? $data['user'] : null;
        $guardians = $student ? $this->userService->getStudentParents($student) : null;
        $files =  $student ? $this->fileService->getUserFiles($student, $request->user()) : null;
        $currentCourse =  $student ? ($data['data']['current_course'] ?? null) : null;
        return $this->render($request, 'Users/BySchool/Student.Show', [
            'user' => $data['data'],
            'currentCourse' => $currentCourse,
            'files' => $files,
            'guardians' => $guardians,
            'genders' => User::genders(),
            'school' => $this->school,
            'breadcrumbs' => Breadcrumbs::generate('schools.student', $this->school, $student),
        ]);
    }

    public function staffView(Request $request, $schoolSlug, $staffIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $data = $this->getStaffData($staffIdAndName);
        $staff = $data ? $data['user'] : null;
        $files = $staff ? $this->fileService->getUserFiles($staff, $request->user()) : null;

        return $this->render($request, 'Users/BySchool/Staff.Show', [
            'user' => $data['data'],
            'files' => $files,
            'genders' => User::genders(),
            'school' => $this->school,
            'breadcrumbs' => Breadcrumbs::generate('schools.staff.show', $this->school, $staff),
        ]);
    }

    public function studentEdit(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $data = $this->getUserData($studentIdAndName);
        $student = $data ? $data['user'] : null;
        return Inertia::render('Users/BySchool/Student.Edit', [
            'school' => $this->school,
            'user' => $student, //->load(['roles']
            'roles' => Role::all(),
            'provinces' => Province::orderBy('order')->get(),
            'countries' => Country::orderBy('order')->get(),
            'genders' => User::genders(),
            'breadcrumbs' => Breadcrumbs::generate('schools.student.edit', $this->school, $student),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function studentUpdate(Request $request, $schoolSlug, $studentIdAndName)
    {
        $this->setSchool($schoolSlug);
        $student = $this->getUserFromUrlParameter($studentIdAndName);
        try {
            $this->userService->updateUser($student, $request->all());
            return redirect()->route('school.student.show', ['school' => $schoolSlug, 'idAndName' => $studentIdAndName])->with('success', 'Estudiante actualizado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function studentEditDiagnoses(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $data = $this->getUserData($studentIdAndName);
        $student = $data ? $data['user'] : null;

        if (!$student) {
            abort(404);
        }
        $canEdit = $request->user()?->can('student.edit') ?? false;
        $userDiagnoses = $student->diagnoses;
        $diagnoses = Diagnosis::getAllWithCategory($canEdit ? null : $userDiagnoses->pluck('id')->toArray());

        return $this->render($request, 'Users/BySchool/Student.EditDiagnoses', [
            'user' => $student,
            'school' => $this->school,
            'genders' => User::genders(),
            'userDiagnoses' => $userDiagnoses,
            'diagnoses' => $diagnoses,
            'canEdit' => $canEdit,
            'breadcrumbs' => Breadcrumbs::generate('schools.student.edit-diagnoses', $this->school, $student),
        ]);
    }

    public function studentUpdateDiagnoses(Request $request, $schoolSlug, $studentIdAndName)
    {
        $this->setSchool($schoolSlug);
        $student = $this->getUserFromUrlParameter($studentIdAndName);

        try {
            $diagnosisService = new DiagnosisService();
            $diagnosisService->updateUserDiagnoses($student, $request->input('diagnoses', []));
            return redirect()->route('school.student.show', ['school' => $schoolSlug, 'idAndName' => $studentIdAndName])->with('success', 'Diagnósticos actualizados exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function staffEdit(Request $request, $schoolSlug, $staffIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $data = $this->getStaffData($staffIdAndName);
        $staff = $data ? $data['user'] : null;

        return Inertia::render('Users/BySchool/Staff.Edit', [
            'school' => $this->school,
            'user' => $staff,
            'roles' => Role::all(),
            'provinces' => Province::orderBy('order')->get(),
            'countries' => Country::orderBy('order')->get(),
            'genders' => User::genders(),
            'breadcrumbs' => Breadcrumbs::generate('schools.staff.edit', $this->school, $staff),
        ]);
    }

    public function staffUpdate(Request $request, $schoolSlug, $staffIdAndName)
    {
        $this->setSchool($schoolSlug);
        $staff = $this->getUserFromUrlParameter($staffIdAndName);
        try {
            $this->userService->updateUser($staff, $request->all());
            return redirect()->route('school.staff.show', ['school' => $schoolSlug, 'idAndName' => $staffIdAndName])->with('success', 'Personal actualizado exitosamente.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
    /**
     * Update the specified user in storage.
     */
    public function uploadImage(Request $request, $schoolSlug, $userIdAndName)
    {
        $this->setSchool($schoolSlug);
        $user = $this->getUserFromUrlParameter($userIdAndName);
        try {
            if ($this->userService->updateUserImage($user, $request))
                return back()->with('success', 'Imagen subida exitosamente');
            else
                return back()->with('error', 'Hubo un problema inesperado al subir la imagen');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function setSchool(string $slug)
    {
        // Find the school by CUE
        $this->school = School::where('slug', $slug)->firstOrFail();

        // Validate if the authenticated user has access to this school
        if (!$this->userService->hasAccessToSchool($this->school->id)) {
            throw new \Spatie\Permission\Exceptions\UnauthorizedException(403, 'No tienes acceso a esta escuela.', null, []);
            // Inertia::render('Errors/403', ['message' => 'No tienes acceso a esta escuela.'])
            //     ->toResponse($request)
            //     ->setStatusCode(403);
        }
        return true;
    }

    private function getUserFromUrlParameter(string $studentIdAndName)
    {
        $id = (int) explode('-', $studentIdAndName)[0];
        return User::where('id', $id)->first();
    }

    private function getUserData(string $studentIdAndName)
    {
        $student = $this->getUserFromUrlParameter($studentIdAndName);
        return ['user' => $student, 'data' => $student ? $this->userService->getStudentUserShowData($student) : null];
    }

    private function getStaffData(string $staffIdAndName)
    {
        $staff = $this->getUserFromUrlParameter($staffIdAndName);
        return ['user' => $staff, 'data' => $staff ? $this->userService->getWorkerUserShowData($staff) : null];
    }
}
