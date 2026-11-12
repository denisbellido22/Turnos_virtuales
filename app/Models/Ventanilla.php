<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ventanilla extends Model
{
    /** @use HasFactory<\Database\Factories\VentanillaFactory> */
    use HasFactory;

    protected $table = 'ventanillas';

    protected $fillable = [
        'area_id',
        'nombre',
        'codigo',
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

    public function identificacion(): string
    {
        return $this->codigo.' · '.$this->nombre;
    }
}
