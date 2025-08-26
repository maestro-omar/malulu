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
use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Validation\ValidationException;

class UserController extends SchoolBaseController
{
    protected $userService;
    protected ?School $school;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('permission:view users')->only(['index', 'trashed']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy', 'restore', 'forceDelete']);
    }

    public function students(Request $request, $slug): Response
    {
        $this->setSchool($slug);

        return $this->render($request, 'Users/BySchool/Students', [
            'users' => $this->userService->getStudentsBySchool($request, $this->school->id),
            'school' => $this->school,
            'filters' => $request->only(['search', 'sort', 'direction']),
            'breadcrumbs' => Breadcrumbs::generate('schools.students', $this->school),
        ]);
    }

    public function student(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $data = $this->getUserData($studentIdAndName);
        $parsedUserData = $data ? $data['data'] : null;
        $student = $data ? $data['user'] : null;
        if ($student) {
            $parents = $student->myParents();
            $files = $student->files;
        }

        return $this->render($request, 'Users/BySchool/Student.Show', [
            'user' => $parsedUserData,
            'genders' => User::genders(),
            'school' => $this->school,
            'breadcrumbs' => Breadcrumbs::generate('schools.student', $this->school, $student),
        ]);
    }

    public function studentEdit(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $student = $this->getUserData($studentIdAndName);

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
        return ['user' => $student, 'data' => $student ? $this->userService->getUserShowData($student) : null];
    }
}
