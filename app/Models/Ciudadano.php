<?php

namespace App\Models;

use Database\Factories\CiudadanoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ciudadano extends Model
{
    /** @use HasFactory<CiudadanoFactory> */
    use HasFactory;

    protected $table = 'ciudadanos';

    protected $fillable = [
        'user_id',
        'nombre',
        'documento',
        'telefono',
        'direccion',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }
}
