<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Biblioteca extends Model
{
    protected $table = 'bibliotecas';

    protected $fillable = [
        'tipo_documento',
        'denominacion',
        'denominacion_numerica',
        'titulo',
        'autor',
        'editorial',
        'año',
        'pais',
    ];

    public function tomos()
    {
        return $this->hasMany(Tomo::class);
    }

}
