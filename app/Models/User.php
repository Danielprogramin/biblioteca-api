<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, SoftDeletes;

    protected $table = 'users';

    protected $guard_name = 'sanctum';

    protected $fillable = [
        'username',
        'persona',
        'email',
        'password',
        'is_admin', // Usuario administrador
        'estado', // Estado activo/inactivo
        'fecha_expiracion',
        'empleado_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // relaciones con otras tablas

    /**
     * relacion con Empleado
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Empleado>
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
