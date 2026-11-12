<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    /** @use HasFactory<\Database\Factories\ServicioFactory> */
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'area_id',
        'nombre',
        'prefijo',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
