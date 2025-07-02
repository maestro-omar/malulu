<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        return response()->json([
            'roles' => $this->roleService->getRoles(),
            'permissions' => $this->roleService->getPermissions()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles',
                'permissions' => 'array',
            ]);

            $role = $this->roleService->createRole($validated);

            return response()->json([
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error en algún campo',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Role $role)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'permissions' => 'array',
            ]);

            $role = $this->roleService->updateRole($role, $validated);

            return response()->json([
                'message' => 'Role updated successfully',
                'data' => $role
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error en algún campo',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Role $role)
    {
        try {
            $this->roleService->deleteRole($role);
            return response()->json([
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}