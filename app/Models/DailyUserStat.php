<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyUserStat extends Model
{
    protected $table = 'daily_user_stats';
    
    protected $fillable = [
        'user_id',
        'tipo_documento',
        'documentos_procesados',
        'fecha',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // Tipos de documento permitidos
    const DOCUMENT_TYPES = [
        'libros' => 'Libros',
        'libros_anillados' => 'Libros Anillados',
        'azs' => 'AZS'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        if ($userId && $userId !== 'todos') {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('fecha', '>=', $from);
        }
        if ($to) {
            $query->where('fecha', '<=', $to);
        }
        return $query;
    }
}