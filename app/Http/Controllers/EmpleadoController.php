<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmpleadoRequest;
use App\Models\Empleado;
use App\Http\Requests\UpdateEmpleadoRequest;

class EmpleadoController extends Controller
{
    /**
     * Obtener todos los empleados.
     */
    public function index()
    {
        try {
            $empleados = Empleado::with(['usuario'])->paginate(15);

            return response()->json($empleados, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener empleados.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear un nuevo empleado.
     */
    public function store(StoreEmpleadoRequest $request)
    {
        try {
            $empleado = Empleado::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente.',
                'data' => $empleado,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el empleado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar un empleado específico.
     */
    public function show(Empleado $empleado)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $empleado->load(['usuario']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el empleado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar un empleado existente.
     */
    public function update(UpdateEmpleadoRequest $request, $id)
    {
        try {
            $empleado = Empleado::findOrFail($id);
            $empleado->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente.',
                'data' => $empleado,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el empleado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un empleado.
     */
    public function destroy($id)
    {
        try {
            $empleado = Empleado::findOrFail($id);
            $empleado->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Empleado no encontrado.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el empleado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
