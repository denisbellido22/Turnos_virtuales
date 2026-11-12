<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    public const ADMINISTRADOR = 'administrador';

    public const EMPLEADO = 'empleado';

    public const CIUDADANO = 'ciudadano';

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
