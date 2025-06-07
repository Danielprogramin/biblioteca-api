<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleActivity extends Model
{
    protected $fillable = [
        'user_id',
        'modulo',
        'fecha',
        'total_acciones',
        'acciones_crear',
        'acciones_editar',
        'acciones_eliminar',
        'acciones_consultar',
        'tiempo_en_modulo_minutos',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
