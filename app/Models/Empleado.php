<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    /** @use HasFactory<\Database\Factories\EmpleadoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_identificacion',
        'numero_identificacion',
        'correo',
        'telefono',
    ];

    // relaciones con otras tablas

    /**
     * relacion con User
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Empleado, User>
     */
    public function usuario()
    {
        return $this->hasOne(User::class, 'empleado_id');
    }
}
