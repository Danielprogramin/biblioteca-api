<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biblioteca;
use Illuminate\Support\Facades\Log;
use App\Traits\logActivity;

class BibliotecaController extends Controller
{
    use logActivity;

    public function index()
    {
        $bibliotecas = Biblioteca::all();
        return response()->json($bibliotecas);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo_documento' => 'required|string',
                'denominacion' => 'required|string',
                'denominacion_numerica' => 'required|string',
                'titulo' => 'required|string',
                'autor' => 'required|string',
                'editorial' => 'nullable|string',
                'tomo' => 'nullable|string',
                'año' => 'required|digits:4',
                'pais' => 'required|string',
                'archivo' => 'required|file|mimes:pdf|max:10240',
            ]);

            if ($request->hasFile('archivo')) {
                $originalName = $request->file('archivo')->getClientOriginalName();
                $filePath = $request->file('archivo')->storeAs('bibliotecas', $originalName, 'public');
                $validated['archivo'] = $filePath;
            }

            $biblioteca = Biblioteca::create($validated);

            // Registrar actividad
            $this->logActivity(
                'crear',
                'documentos',
                'Se creó un nuevo documento: ' . $biblioteca->titulo,
                [
                    'tipo' => $biblioteca->tipo_documento,
                    'denominacion' => $biblioteca->denominacion,
                    'codigo' => $biblioteca->denominacion_numerica
                ]
            );

            return response()->json([
                'message' => 'Documento guardado exitosamente',
                'data' => $biblioteca
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al guardar documento: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        return response()->json($biblioteca);
    }

    public function update(Request $request, string $id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        $datosAntes = $biblioteca->toArray();

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

        // Registrar actividad
        $this->logActivity(
            'actualizar',
            'documentos',
            'Se actualizó el documento: ' . $biblioteca->titulo,
            [
                'antes' => $datosAntes,
                'después' => $biblioteca->toArray()
            ]
        );

        return response()->json($biblioteca);
    }

    public function destroy(string $id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        $datosAntes = $biblioteca->toArray();
        $titulo = $biblioteca->titulo;

        $biblioteca->delete();

        // Registrar actividad
        $this->logActivity(
            'eliminar',
            'documentos',
            'Se eliminó el documento: ' . $titulo,
            $datosAntes
        );

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }
}
