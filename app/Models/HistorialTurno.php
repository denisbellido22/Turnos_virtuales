<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialTurno extends Model
{
    public $timestamps = false;

    protected $table = 'historial_turnos';

    protected $fillable = [
        'turno_id',
        'user_id',
        'ventanilla_id',
        'estado_anterior',
        'estado_nuevo',
        'accion',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(Ventanilla::class);
    }

    public function accionEtiqueta(): string
    {
        return match ($this->accion) {
            'crear' => 'Turno generado',
            'llamar' => 'Turno llamado',
            'repetir_llamado' => 'Llamado repetido',
            'atender' => 'Turno atendido',
            'ausente' => 'Ciudadano ausente',
            'cancelar' => 'Turno cancelado',
            default => ucfirst(str_replace('_', ' ', $this->accion)),
        };
    }
}
