<?php

namespace App\Http\Controllers\School;

use App\Models\Entities\User;
use App\Models\Entities\School;
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


    private function setSchool(string $slug)
    {
        // Find the school by CUE
        $this->school = School::where('slug', $slug)->firstOrFail();

        // Validate if the authenticated user has access to this school
        if (!$this->userService->hasAccessToSchool($this->school->id)) {
            abort(403, 'No tienes acceso a esta escuela.');
        }
    }
}
