<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\UserSession;
use App\Models\ModuleActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    // Listado de logs con filtros y búsqueda
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

        return $query->latest()->get();
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

    // Productividad diaria por usuario y tipo de documento (ejemplo)
    public function dailyStats(Request $request)
    {
        // Suponiendo que tienes un modelo ModuleActivity para esto
        $stats = ModuleActivity::select(
                'fecha',
                'user_id',
                DB::raw('SUM(acciones_crear) as libros'),
                DB::raw('SUM(acciones_editar) as librosAnillados'),
                DB::raw('SUM(acciones_eliminar) as azs'),
                DB::raw('SUM(total_acciones) as total')
            )
            ->groupBy('fecha', 'user_id')
            ->with('user:id,username')
            ->get()
            ->map(function ($row) {
                return [
                    'fecha' => $row->fecha,
                    'usuario' => $row->user->username ?? '',
                    'libros' => $row->libros,
                    'librosAnillados' => $row->librosAnillados,
                    'azs' => $row->azs,
                    'total' => $row->total,
                ];
            });

        return response()->json($stats);
    }
}