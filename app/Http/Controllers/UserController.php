<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Empleado;
use App\Models\User;
use App\Traits\ApiResponses;

class UserController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $usuarios =User::paginate(15);
        // return response()->json([
        //     'success' => true,
        //     'data' => $usuarios,
        // ], 200);
        $usuarios = User::paginate(15);

        return response()->json([
            'success' => true,
            'data' => $usuarios->items(),
            'meta' => [
                'current_page' => $usuarios->currentPage(),
                'last_page' => $usuarios->lastPage(),
                'per_page' => $usuarios->perPage(),
                'total' => $usuarios->total(),
            ],
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'is_admin' => $request->role === 'Admin',
                'estado' => $request->estado ?? true,
                'fecha_expiracion' => $request->fecha_expiracion,
            ]);

            // ✅ Asignar el rol usando Spatie
            if ($request->has('role')) {
                $user->assignRole($request->role);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un usuario específico.
     */
    public function show($id)
    {
        try {
            $usuario = User::get()->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $usuario,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Actualizar datos básicos
            $user->update([
                'username' => $request->username,
                'is_admin' => $request->role === 'Admin',
                'estado' => $request->estado ?? $user->estado,
                'fecha_expiracion' => $request->fecha_expiracion ?? $user->fecha_expiracion,
            ]);

            // ✅ Asignar nuevo rol si viene en la solicitud
            if ($request->has('role')) {
                $user->syncRoles([$request->role]); // Quita roles anteriores y asigna el nuevo
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $usuario = User::findOrFail($id);
            $usuario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
