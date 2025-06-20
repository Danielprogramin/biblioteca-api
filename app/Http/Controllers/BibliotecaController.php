<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biblioteca;
use Illuminate\Support\Facades\Log;
use App\Traits\logActivity;
use App\Models\DailyUserStat;
use App\Models\Tomo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class BibliotecaController extends Controller
{
    use logActivity;

    public function index()
    {
        $bibliotecas = Biblioteca::with('tomos')->get();
        return response()->json($bibliotecas);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'tipo_documento' => 'required|string',
                'denominacion' => 'required|string',
                'denominacion_numerica' => 'required|string|unique:bibliotecas,denominacion_numerica',
                'titulo' => 'required|string',
                'autor' => 'required|string',
                'editorial' => 'nullable|string',
                'año' => 'required|digits:4',
                'pais' => 'required|string',
                'tomos' => 'required|array|min:1',
                'tomos.*.numero' => 'required|integer|min:1',
                'tomos.*.archivo' => 'required|file|mimetypes:application/pdf,application/x-pdf|max:1000000',
            ]);

            // Convertir a mayúsculas los campos de texto
            $bibliotecaData = [
                'tipo_documento' => mb_strtoupper($validated['tipo_documento']),
                'denominacion' => mb_strtoupper($validated['denominacion']),
                'denominacion_numerica' => mb_strtoupper($validated['denominacion_numerica']),
                'titulo' => mb_strtoupper($validated['titulo']),
                'autor' => mb_strtoupper($validated['autor']),
                'editorial' => isset($validated['editorial']) ? mb_strtoupper($validated['editorial']) : null,
                'año' => $validated['año'],
                'pais' => mb_strtoupper($validated['pais']),
            ];

            // Crear registro principal en bibliotecas
            $biblioteca = Biblioteca::create($bibliotecaData);

            // Procesar cada tomo
            foreach ($request->tomos as $tomoData) {
                $file = $tomoData['archivo'];
                $originalName = $file->getClientOriginalName();

                // Obtener nombre sin extensión
                $nombreSinExtension = pathinfo($originalName, PATHINFO_FILENAME);

                // Limpiar nombre: quitar caracteres no deseados
                $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreSinExtension);
                $nombreLimpio = trim($nombreLimpio, "_");

                // // Construir nuevo nombre con formato
                // $nuevoNombre = $bibliotecaData['denominacion_numerica'] . '_' .
                //                 $nombreLimpio . '_' .
                //                 $bibliotecaData['año'] . '.' .
                //                 $file->getClientOriginalExtension();
                // Construir nuevo nombre con formato incluyendo tomo
                $nuevoNombre = $bibliotecaData['denominacion_numerica'] . '_' .
                    $nombreLimpio . '_' .
                    $bibliotecaData['año'] . '_tomo' .
                    $tomoData['numero'] . '.' .
                    $file->getClientOriginalExtension();

                // Mover archivo al directorio con nuevo nombre
                $file->move('C:/archivos_biblioteca', $nuevoNombre);

                // Crear registro de tomo
                $biblioteca->tomos()->create([
                    'numero' => $tomoData['numero'],
                    'archivo' => $nuevoNombre
                ]);
            }

            // Registrar actividad
            $this->logActivity(
                'crear',
                'documentos',
                'Se creó un nuevo documento: ' . $biblioteca->titulo,
                [
                    'tipo' => $biblioteca->tipo_documento,
                    'denominacion' => $biblioteca->denominacion,
                    'codigo' => $biblioteca->denominacion_numerica,
                    'tomos' => count($request->tomos)
                ]
            );

            // Registrar en las estadísticas diarias
            $this->recordDailyStat($biblioteca->tipo_documento);

            DB::commit();

            return response()->json([
                'message' => 'Documento y tomos guardados exitosamente',
                'data' => [
                    'biblioteca' => $biblioteca,
                    'tomos' => $biblioteca->tomos
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
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
        DB::beginTransaction();

        try {
            $biblioteca = Biblioteca::findOrFail($id);
            $datosAntes = $biblioteca->toArray();

            $validated = $request->validate([
                'tipo_documento' => 'sometimes|required|string',
                'denominacion' => 'sometimes|required|string',
                'denominacion_numerica' => 'sometimes|required|string|unique:bibliotecas,denominacion_numerica,' . $biblioteca->id,
                'titulo' => 'sometimes|required|string',
                'autor' => 'sometimes|required|string',
                'editorial' => 'sometimes|nullable|string',
                'año' => 'sometimes|required|digits:4',
                'pais' => 'sometimes|required|string',
                'tomos' => 'sometimes|array|min:1',
                'tomos.*.numero' => 'required|integer|min:1',
                'tomos.*.archivo' => 'sometimes|required|file|mimetypes:application/pdf,application/x-pdf|max:1000000',
                'tomos.*.id' => 'sometimes|integer|exists:tomos,id',
                'tomos_eliminados' => 'sometimes|array',
                'tomos_eliminados.*' => 'integer|exists:tomos,id',
            ]);

            // Convertir a mayúsculas los campos de texto
            $updateData = [];
            $textFields = ['tipo_documento', 'denominacion', 'denominacion_numerica', 'titulo', 'autor', 'editorial', 'pais'];
            foreach ($textFields as $field) {
                if (isset($validated[$field])) {
                    $updateData[$field] = mb_strtoupper($validated[$field]);
                }
            }
            if (isset($validated['año'])) {
                $updateData['año'] = $validated['año'];
            }

            // Actualizar el documento principal
            $biblioteca->update($updateData);

            // Procesar tomos eliminados
            if (isset($validated['tomos_eliminados'])) {
                $tomosEliminados = Tomo::whereIn('id', $validated['tomos_eliminados'])->get();
                foreach ($tomosEliminados as $tomo) {
                    if ($tomo->archivo && file_exists('C:/archivos_biblioteca/' . $tomo->archivo)) {
                        @unlink('C:/archivos_biblioteca/' . $tomo->archivo);
                    }
                    $tomo->delete();
                }
            }

            // Procesar tomos (actualizar existentes y agregar nuevos)
            if (isset($validated['tomos'])) {
                foreach ($validated['tomos'] as $tomoData) {
                    if (isset($tomoData['id'])) {
                        $tomo = Tomo::find($tomoData['id']);
                        if ($tomo) {
                            $tomo->numero = $tomoData['numero'];

                            if (isset($tomoData['archivo']) && $tomoData['archivo'] instanceof \Illuminate\Http\UploadedFile) {
                                if ($tomo->archivo && file_exists('C:/archivos_biblioteca/' . $tomo->archivo)) {
                                    @unlink('C:/archivos_biblioteca/' . $tomo->archivo);
                                }

                                $file = $tomoData['archivo'];
                                $originalName = $file->getClientOriginalName();
                                $nombreSinExtension = pathinfo($originalName, PATHINFO_FILENAME);
                                $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreSinExtension);
                                $nombreLimpio = trim($nombreLimpio, "_");
                                $nuevoNombre = $biblioteca->denominacion_numerica . '_' .
                                    $nombreLimpio . '_' .
                                    $biblioteca->año . '_tomo' .
                                    $tomoData['numero'] . '.' .
                                    $file->getClientOriginalExtension();

                                $file->move('C:/archivos_biblioteca', $nuevoNombre);
                                $tomo->archivo = $nuevoNombre;
                            }

                            $tomo->save();
                        }
                    } else {
                        // Es un nuevo tomo
                        $file = $tomoData['archivo'];
                        $originalName = $file->getClientOriginalName();
                        $nombreSinExtension = pathinfo($originalName, PATHINFO_FILENAME);
                        $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreSinExtension);
                        $nombreLimpio = trim($nombreLimpio, "_");
                        $nuevoNombre = $biblioteca->denominacion_numerica . '_' .
                            $nombreLimpio . '_' .
                            $biblioteca->año . '_tomo' .
                            $tomoData['numero'] . '.' .
                            $file->getClientOriginalExtension();

                        $file->move('C:/archivos_biblioteca', $nuevoNombre);

                        $biblioteca->tomos()->create([
                            'numero' => $tomoData['numero'],
                            'archivo' => $nuevoNombre
                        ]);
                    }
                }
            }

            // Registrar actividad
            $this->logActivity(
                'editar',
                'documentos',
                'Se actualizó el documento: ' . $biblioteca->titulo,
                [
                    'antes' => $datosAntes,
                    'después' => $biblioteca->toArray()
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Documento actualizado exitosamente',
                'data' => $biblioteca->load('tomos')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar documento: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
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

    /**
     * Descargar archivo adjunto con nombre personalizado
     */
    public function descargarArchivo(Request $request) // ✅ Elimina $id
    {
        $archivoSolicitado = $request->query('file');
        $ruta = 'C:/archivos_biblioteca/' . $archivoSolicitado;

        if (!file_exists($ruta)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return response()->download($ruta, $archivoSolicitado, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Retorna todas las denominaciones de la tabla biblioteca para gráficas
     */
    public function denominaciones()
    {
        $denominaciones = Biblioteca::pluck('denominacion');
        return response()->json([
            'denominaciones' => $denominaciones
        ]);
    }

    /**
     * Retorna todos los tipos de documento de la tabla biblioteca para gráficas
     */
    public function tiposDocumento()
    {
        $tipos = Biblioteca::pluck('tipo_documento');
        return response()->json([
            'tipos_documento' => $tipos
        ]);
    }

    public function actividadMensual()
{
    $inicioMes = Carbon::now()->startOfMonth();
    $finMes = Carbon::now()->endOfMonth();

    // Documentos registrados por día (acción: crear)
    $registros = DB::table('audit_logs')
        ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
        ->where('accion', 'crear')
        ->whereBetween('created_at', [$inicioMes, $finMes])
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('fecha')
        ->get();

    // Consultas por día (acción: consultar)
    $consultas = DB::table('audit_logs')
        ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
        ->where('accion', 'consultar')
        ->whereBetween('created_at', [$inicioMes, $finMes])
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('fecha')
        ->get();

    return response()->json([
        'registros' => $registros,
        'consultas' => $consultas,
    ]);
}

}
