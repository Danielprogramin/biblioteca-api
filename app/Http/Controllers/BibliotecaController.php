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
        $validated = $request->validate([
            'tipo_documento' => 'required|string',
            'denominacion' => 'required|string',
            'denominacion_numerica' => 'required|string',
            'titulo' => 'required|string',
            'autor' => 'required|string',
            'editorial' => 'required|string',
            'tomo' => 'nullable|string',
            'año' => 'required|integer',
            'pais' => 'required|string',
            'archivo' => 'nullable|string',
        ]);

        $biblioteca = Biblioteca::create($validated);
        return response()->json($biblioteca, 201);
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
            'archivo' => 'nullable|string',
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
