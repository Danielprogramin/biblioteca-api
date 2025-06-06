<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biblioteca;

class BibliotecaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bibliotecas = Biblioteca::all();
        return response()->json($bibliotecas);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        try {
            // Validación corregida
            $validated = $request->validate([
                'tipo_documento' => 'required|string',
                'denominacion' => 'required|string|size:2',
                'denominacion_numerica' => 'required|string',
                'titulo' => 'required|string',
                'autor' => 'required|string',
                'editorial' => 'nullable|string',  // Cambiado a nullable
                'tomo' => 'nullable|string',
                'año' => 'required|digits:4',     // Cambiado a digits:4
                'pais' => 'required|string',
                'archivo' => 'required|file|mimes:pdf|max:10240', // Requerido y validación de PDF
            ]);

            // Almacenar archivo en disco público
            $filePath = $request->file('archivo')->store('bibliotecas', 'public');
            $validated['archivo'] = $filePath;

            $biblioteca = Biblioteca::create($validated);

            return response()->json([
                'message' => 'Documento guardado exitosamente',
                'data' => $biblioteca
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Capturar errores de validación específicos
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Log detallado del error
            Log::error('Error al guardar documento: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        return response()->json($biblioteca);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $biblioteca = Biblioteca::findOrFail($id);

        $validated = $request->validate([
            'tipo_documento' => 'sometimes|required|string',
            'denominacion' => 'sometimes|required|string',
            'denominacion_numerica' => 'sometimes|required|string',
            'titulo' => 'sometimes|required|string',
            'autor' => 'sometimes|required|string',
            'editorial' => 'sometimes|required|string',
            'tomo' => 'nullable|string',
            'año' => 'sometimes|required|integer',
            'pais' => 'sometimes|required|string',
            'archivo' => 'nullable|file',
        ]);

        $biblioteca->update($validated);
        return response()->json($biblioteca);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        $biblioteca->delete();
        return response()->json(['message' => 'Registro eliminado correctamente']);
    }
}
