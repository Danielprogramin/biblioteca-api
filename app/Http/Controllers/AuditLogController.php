<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\UserSession;
use App\Models\ModuleActivity;
use App\Models\DailyUserStat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    // Listado de logs con filtros y búsqueda y paginación
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->accion && $request->accion !== 'todas') {
            $query->where('accion', $request->accion);
        }
        if ($request->modulo && $request->modulo !== 'todos') {
            $query->where('modulo', $request->modulo);
        }
        if ($request->usuario && $request->usuario !== 'todos') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username', $request->usuario);
            });
        }
        if ($request->search) {
            $query->where('descripcion', 'like', "%{$request->search}%");
        }

        $perPage = $request->input('per_page', 15); // Puedes cambiar el valor por defecto
        return $query->latest()->paginate($perPage);
    }

    // Exportar log a CSV
    public function export(Request $request)
    {
        $query = AuditLog::query();

        // (Opcional) Aplica los mismos filtros que en index()
        if ($request->filled('usuario') && $request->usuario !== 'todos') {
            $query->where('usuario', $request->usuario);
        }
        if ($request->filled('accion') && $request->accion !== 'todas') {
            $query->where('accion', $request->accion);
        }
        if ($request->filled('modulo') && $request->modulo !== 'todos') {
            $query->where('modulo', $request->modulo);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('usuario', 'like', "%$search%")
                  ->orWhere('descripcion', 'like', "%$search%")
                  ->orWhere('detalles', 'like', "%$search%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs.csv"',
        ];

        $columns = [
            'ID', 'Usuario', 'Acción', 'Módulo', 'Descripción', 'Detalles', 'Fecha/Hora', 'IP', 'Navegador'
        ];

        $callback = function () use ($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->usuario,
                    $log->accion,
                    $log->modulo,
                    $log->descripcion,
                    $log->detalles,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->ip_address,
                    $log->navegador,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    // Estadísticas por usuario
    public function userStats()
    {
        $stats = AuditLog::select(
                'usuario',
                DB::raw("SUM(CASE WHEN accion = 'crear' AND modulo = 'documentos' THEN 1 ELSE 0 END) as documentosCreados"),
                DB::raw("SUM(CASE WHEN accion = 'editar' AND modulo = 'documentos' THEN 1 ELSE 0 END) as documentosEditados"),
                DB::raw("SUM(CASE WHEN accion = 'consultar' AND modulo = 'documentos' THEN 1 ELSE 0 END) as consultasRealizadas"),
                DB::raw("MAX(created_at) as ultimaActividad")
            )
            ->groupBy('usuario')
            ->get();

        // (Opcional) Puedes calcular el tiempo de sesión usando UserSession
        foreach ($stats as $stat) {
            $stat->tiempoSesion = UserSession::where('user_id', function($q) use ($stat) {
                $q->select('id')->from('users')->where('username', $stat->usuario)->limit(1);
            })->sum('duracion_minutos');
            $stat->tiempoSesion = $stat->tiempoSesion ? gmdate("H\h i\m", $stat->tiempoSesion * 60) : "0h 0m";
        }

        return response()->json($stats);
    }

    public function getDailyStats(Request $request)
{
    $request->validate([
        'fecha_desde' => 'nullable|date',
        'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        'usuario' => 'nullable|exists:users,id',
    ]);

    // Consulta base para PostgreSQL
    $query = AuditLog::query()
        ->where('accion', 'crear')
        ->where('modulo', 'documentos')
        ->whereNotNull('detalles')
        ->select(
            DB::raw("DATE(created_at) as fecha"),
            'user_id',
            DB::raw("COUNT(*) as total"),
            DB::raw("SUM(CASE WHEN detalles::json->>'tipo' = 'LIBROS' THEN 1 ELSE 0 END) as libros"),
            DB::raw("SUM(CASE WHEN detalles::json->>'tipo' = 'LIBROS_ANILLADOS' THEN 1 ELSE 0 END) as libros_anillados"),
            DB::raw("SUM(CASE WHEN detalles::json->>'tipo' = 'AZS' THEN 1 ELSE 0 END) as azs")
        )
        ->groupBy(DB::raw('DATE(created_at)'), 'user_id') // PostgreSQL requiere agrupar por la expresión, no el alias
        ->orderBy('fecha', 'desc');

    // Filtrar por rango de fechas
    if ($request->fecha_desde) {
        $query->whereDate('created_at', '>=', $request->fecha_desde);
    }

    if ($request->fecha_hasta) {
        $query->whereDate('created_at', '<=', $request->fecha_hasta);
    }

    // Filtrar por usuario
    if ($request->usuario && $request->usuario !== 'todos') {
        $query->where('user_id', $request->usuario);
    }

    $stats = $query->get();

    // Calcular totales del período
    $periodTotals = [
        'libros' => $stats->sum('libros'),
        'libros_anillados' => $stats->sum('libros_anillados'),
        'azs' => $stats->sum('azs'),
        'total' => $stats->sum('total')
    ];

    return response()->json([
        'success' => true,
        'data' => $stats,
        'period_totals' => $periodTotals,
    ]);
}

    protected function applyQuickFilter($filter)
    {
        $today = Carbon::today();

        switch ($filter) {
            case 'hoy':
                return [
                    'from' => $today->format('Y-m-d'),
                    'to' => $today->format('Y-m-d')
                ];
            case 'ayer':
                return [
                    'from' => $today->subDay()->format('Y-m-d'),
                    'to' => $today->format('Y-m-d')
                ];
            case 'ultima-semana':
                return [
                    'from' => $today->subWeek()->startOfWeek()->format('Y-m-d'),
                    'to' => $today->subWeek()->endOfWeek()->format('Y-m-d')
                ];
            case 'esta-semana':
                return [
                    'from' => $today->startOfWeek()->format('Y-m-d'),
                    'to' => $today->endOfWeek()->format('Y-m-d')
                ];
            case 'este-mes':
                return [
                    'from' => $today->startOfMonth()->format('Y-m-d'),
                    'to' => $today->endOfMonth()->format('Y-m-d')
                ];
            case 'mes-anterior':
                return [
                    'from' => $today->subMonth()->startOfMonth()->format('Y-m-d'),
                    'to' => $today->endOfMonth()->format('Y-m-d')
                ];
            default:
                return [
                    'from' => $today->subMonth()->format('Y-m-d'),
                    'to' => $today->format('Y-m-d')
                ];
        }
    }

    public function exportDailyStats(Request $request)
    {
        // Similar a dailyStats pero para exportar a Excel
        $stats = $this->getFilteredStats($request);

        return Excel::download(new DailyStatsExport($stats), 'productividad-diaria.xlsx');
    }
}
