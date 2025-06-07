<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyUserStat extends Model
{
    protected $fillable = [
        'user_id',
        'fecha',
        'documentos_creados',
        'documentos_editados',
        'documentos_eliminados',
        'consultas_realizadas',
        'libros_creados',
        'libros_anillados_creados',
        'azs_creados',
        'tiempo_sesion_minutos',
        'primera_actividad',
        'ultima_actividad',
    ];

    protected $casts = [
        'fecha' => 'date',
        'primera_actividad' => 'datetime:H:i:s',
        'ultima_actividad' => 'datetime:H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
