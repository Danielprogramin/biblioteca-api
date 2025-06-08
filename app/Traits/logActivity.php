<?php
namespace App\Traits;

use App\Models\AuditLog;

trait logActivity
{
    public function logActivity($accion, $modulo, $descripcion, $detalles = null)
    {
        // $user = Auth::user();
        
        AuditLog::create([
            'user_id' => auth()->user()->id,
            'usuario' => auth()->user()->username ?? auth()->user()->name ?? 'desconocido',
            'accion' => $accion,
            'modulo' => $modulo,
            'descripcion' => $descripcion,
            'detalles' => is_array($detalles) ? json_encode($detalles) : $detalles,
            'ip' => request()->ip(),
            'navegador' => request()->userAgent()
        ]);
    }
}
