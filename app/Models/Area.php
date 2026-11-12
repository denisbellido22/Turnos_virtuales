<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    /** @use HasFactory<\Database\Factories\AreaFactory> */
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    public function ventanillas(): HasMany
    {
        return $this->hasMany(Ventanilla::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
