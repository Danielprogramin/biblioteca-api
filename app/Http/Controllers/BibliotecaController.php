<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biblioteca;
use Illuminate\Support\Facades\Log;
use App\Traits\logActivity;
use App\Models\DailyUserStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


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

        // Convertir a mayúsculas los campos deseados
        $validated['tipo_documento'] = mb_strtoupper($validated['tipo_documento']);
        $validated['denominacion'] = mb_strtoupper($validated['denominacion']);
        $validated['denominacion_numerica'] = mb_strtoupper($validated['denominacion_numerica']);
        $validated['titulo'] = mb_strtoupper($validated['titulo']);
        $validated['autor'] = mb_strtoupper($validated['autor']);
        if (isset($validated['editorial'])) {
            $validated['editorial'] = mb_strtoupper($validated['editorial']);
        }
        if (isset($validated['tomo'])) {
            $validated['tomo'] = mb_strtoupper($validated['tomo']);
        }
        $validated['pais'] = mb_strtoupper($validated['pais']);

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

        // Registrar en las estadísticas diarias
        $this->recordDailyStat($biblioteca->tipo_documento);

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
    protected function recordDailyStat($tipoDocumento)
{
    $today = Carbon::today()->toDateString();
    $userId = Auth::id();

    $stat = DailyUserStat::firstOrNew([
        'user_id' => $userId,
        'fecha' => $today,
        'tipo_documento' => $tipoDocumento,
    ]);

    // Si ya existe, incrementa, si no, inicializa con 1
    $stat->documentos_procesados = $stat->exists ? $stat->documentos_procesados + 1 : 1;
    $stat->save();
}
}
