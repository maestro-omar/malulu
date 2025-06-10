<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public function getRoles()
    {
        return Role::with('permissions')->get();
    }

    public function getPermissions()
    {
        return Permission::all();
    }

    public function createRole(array $data)
    {
        $role = Role::create(['name' => $data['name']]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function updateRole(Role $role, array $data)
    {
        $role->update(['name' => $data['name']]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function deleteRole(Role $role)
    {
        if ($role->name === 'admin') {
            throw new \Exception('No se puede eliminar el rol de administrador.');
        }

        return $role->delete();
    }
}