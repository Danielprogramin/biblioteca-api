<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    use ApiResponses;

    public function index()
    {
        try {
            $roles = Role::paginate(15);
            return $this->success('Roles obtenidos correctamente.', $roles);
        } catch (\Exception $e) {
            return $this->error('Error al obtener roles.', 500, $e->getMessage());
        }
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'descripcion' => $request->descripcion,
                'guard_name' => $request->guard_name ?? 'sanctum',
            ]);

            return $this->success('Rol creado correctamente.', $role, 201);
        } catch (\Exception $e) {
            return $this->error('Error al crear el rol.', 500, $e->getMessage());
        }
    }

    public function show(Role $role)
    {
        return $this->success('Rol obtenido correctamente.', $role);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            $role->update([
                'name' => $request->name,
                'descripcion' => $request->descripcion,
                'guard_name' => $request->guard_name ?? $role->guard_name,
            ]);

            return $this->success('Rol actualizado correctamente.', $role);
        } catch (\Exception $e) {
            return $this->error('Error al actualizar el rol.', 500, $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        try {
            $role->delete();
            return $this->success('Rol eliminado correctamente.');
        } catch (\Exception $e) {
            return $this->error('Error al eliminar el rol.', 500, $e->getMessage());
        }
    }
}
