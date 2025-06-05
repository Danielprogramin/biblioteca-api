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
        'tomo',
        'año',
        'pais',
        'archivo'
    ];

}
