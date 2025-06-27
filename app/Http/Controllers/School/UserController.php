<?php

namespace App\Http\Controllers\School;

use App\Models\Entities\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;

class UserController extends SchoolBaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('permission:view users')->only(['index', 'trashed']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy', 'restore', 'forceDelete']);
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request, $cue): Response
    {
        // Find the school by CUE
        $school = \App\Models\Entities\School::where('cue', $cue)->firstOrFail();

        // Validate if the authenticated user has access to this school
        if (!$this->userService->hasAccessToSchool($school->id)) {
            abort(403, 'No tienes acceso a esta escuela.');
        }

        return Inertia::render('Users/Index', [
            'users' => $this->userService->getUsersBySchool($request, $school->id),
            'school' => $school
        ]);
    }

    private function getRoleShortName($roleName)
    {
        $shortNames = [
            'Administrador' => 'Admin',
            'Director' => 'Dir',
            'Regente' => 'Reg',
            'Secretario' => 'Sec',
            'Maestra/o de Grado' => 'Grado',
            'Responsable' => 'Resp'
        ];

        return $shortNames[$roleName] ?? $roleName;
    }

    /**
     * Display a listing of the trashed users.
     */
    public function trashed(): Response
    {
        return Inertia::render('Users/Trashed', [
            'users' => $this->userService->getTrashedUsers(),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->userService->createUser($request->all());
            return redirect()->route('users.index')
                ->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => bcrypt($validated['password']),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of admin users
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede eliminar un usuario administrador.');
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'No puede eliminar su propia cuenta desde esta sección.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Restore the specified user from trash.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.trashed')
            ->with('success', 'Usuario restaurado exitosamente.');
    }

    /**
     * Permanently delete the specified user.
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        // Prevent permanent deletion of admin users
        if ($user->hasRole('admin')) {
            return redirect()->route('users.trashed')
                ->with('error', 'No se puede eliminar permanentemente un usuario administrador.');
        }

        $user->forceDelete();

        return redirect()->route('users.trashed')
            ->with('success', 'Usuario eliminado permanentemente.');
    }

    public function show(User $user): Response
    {
        $transformedUser = $this->userService->getUserShowData($user);

        return Inertia::render('Users/Show', [
            'user' => $transformedUser
        ]);
    }
}