<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'usuario',
        'accion',
        'modulo',
        'descripcion',
        'detalles',
        'tabla_afectada',
        'registro_id',
        'datos_anteriores',
        'datos_nuevos',
        'ip_address',
        'user_agent',
        'navegador',
        'session_id',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
