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
            'filters' => $request->only(['search']),
            'breadcrumbs' => Breadcrumbs::generate('schools.students', $this->school),
        ]);
    }

    public function student(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $student = $this->getStudent($studentIdAndName);

        return $this->render($request, 'Users/BySchool/Student.Show', [
            'user' => $student,
            'genders' => User::genders(),
            'school' => $this->school,
            'breadcrumbs' => Breadcrumbs::generate('schools.student', $this->school, $student),
        ]);
    }

    public function studentEdit(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $student = $this->getStudent($studentIdAndName);

        return Inertia::render('Users/BySchool/Student.Edit', [
            'user' => $student, //->load(['roles']
            'school' => $this->school,
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
    public function studentUpdate(Request $request, $schoolSlug, $studentIdAndName): Response
    {
        $this->setSchool($schoolSlug);
        $student = $this->getStudent($studentIdAndName);
        try {
            $this->userService->updateUser($student, $request->all());
            return redirect()->back()->with('success', 'Estudiante actualizado exitosamente.');
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

    private function setSchool(string $slug)
    {
        // Find the school by CUE
        $this->school = School::where('slug', $slug)->firstOrFail();

        // Validate if the authenticated user has access to this school
        if (!$this->userService->hasAccessToSchool($this->school->id)) {
            return Inertia::render('Errors/403')
                // ->toResponse($request)
                ->setStatusCode(403);
            // abort(403, 'No tienes acceso a esta escuela.');
        }
    }

    private function getStudent(string $studentIdAndName)
    {
        $id = (int) explode('-', $studentIdAndName)[0];
        $student = User::where('id', $id)->first();
        return $student ? $this->userService->getUserShowData($student) : null;
    }
}
