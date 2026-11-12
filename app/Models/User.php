<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
        'area_id',
        'ventanilla_id',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(Ventanilla::class);
    }

    public function ciudadano(): HasOne
    {
        return $this->hasOne(Ciudadano::class);
    }

    public function turnosAtendidos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function esAdministrador(): bool
    {
        return $this->rol?->nombre === Rol::ADMINISTRADOR;
    }

    public function esEmpleado(): bool
    {
        return $this->rol?->nombre === Rol::EMPLEADO;
    }

    public function esCiudadano(): bool
    {
        return $this->rol?->nombre === Rol::CIUDADANO;
    }

    public function rutaPanel(): string
    {
        return match ($this->rol?->nombre) {
            Rol::ADMINISTRADOR => route('admin.dashboard'),
            Rol::EMPLEADO => route('atencion.panel'),
            default => route('ciudadano.turnos.index'),
        };
    }
}
