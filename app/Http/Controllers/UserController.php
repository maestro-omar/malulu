<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view users')->only(['index', 'trashed']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy', 'restore', 'forceDelete']);
    }

    /**
     * Display a listing of the users.
     */
    public function index(): Response
    {
        $users = User::with('allRolesAcrossTeams')->paginate(10);

        // Transform the data to include roles in the expected format
        $transformedUsers = json_decode(json_encode($users), true);
        $transformedUsers['data'] = collect($transformedUsers['data'])->map(function ($user) {
            // Get unique school IDs from roles
            $schoolIds = collect($user['all_roles_across_teams'])
                ->pluck('pivot.team_id')
                ->filter()  // Remove null values
                ->unique()
                ->values()
                ->toArray();

            // Debug log for school IDs
            Log::info('School IDs for user ' . $user['id'] . ':', [
                'school_ids' => $schoolIds,
                'roles' => $user['all_roles_across_teams']
            ]);

            // Get schools for these IDs
            $schools = \App\Models\School::whereIn('id', $schoolIds)->get();

            // Debug log for found schools
            Log::info('Found schools for user ' . $user['id'] . ':', [
                'schools' => $schools->toArray()
            ]);

            $user['roles'] = collect($user['all_roles_across_teams'])->map(function ($role) {
                return [
                    'id' => $role['id'],
                    'name' => $role['name'],
                    'short' => $this->getRoleShortName($role['name']),
                    'key' => $role['key'],
                    'team_id' => $role['team_id']
                ];
            })->toArray();
            unset($user['all_roles_across_teams']);

            // Add schools to user data
            $user['schools'] = $schools->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'short' => $school->short
                ];
            })->toArray();

            return $user;
        })->toArray();

        // Debug information
        Log::info('Users with roles and schools:', [
            'users' => collect($transformedUsers['data'])->map(function ($user) {
                return [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'roles' => $user['roles'],
                    'schools' => $user['schools']
                ];
            })->toArray()
        ]);

        return Inertia::render('Users/Index', [
            'users' => $transformedUsers,
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
            'users' => User::onlyTrashed()->with('roles')->paginate(10),
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
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
        $user->load(['allRolesAcrossTeams']);

        // Transform the data to include roles and schools
        $transformedUser = $user->toArray();
        
        // Get unique school IDs from roles
        $schoolIds = collect($user->allRolesAcrossTeams)
            ->pluck('pivot.team_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Get schools for these IDs
        $schools = \App\Models\School::whereIn('id', $schoolIds)->get();

        $transformedUser['roles'] = collect($user->allRolesAcrossTeams)->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'short' => $this->getRoleShortName($role->name),
                'key' => $role->key,
                'team_id' => $role->pivot->team_id
            ];
        })->toArray();

        // Add schools to user data
        $transformedUser['schools'] = $schools->map(function ($school) {
            return [
                'id' => $school->id,
                'name' => $school->name,
                'short' => $school->short
            ];
        })->toArray();

        return Inertia::render('Users/Show', [
            'user' => $transformedUser
        ]);
    }
} 