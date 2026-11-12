<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_LLAMANDO = 'llamando';

    public const ESTADO_ATENDIDO = 'atendido';

    public const ESTADO_AUSENTE = 'ausente';

    public const ESTADO_CANCELADO = 'cancelado';

    public const ESTADOS_ACTIVOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_LLAMANDO,
    ];

    protected $table = 'turnos';

    protected $fillable = [
        'codigo',
        'numero',
        'fecha',
        'servicio_id',
        'area_id',
        'ciudadano_id',
        'ventanilla_id',
        'user_id',
        'estado',
        'llamado_at',
        'atendido_at',
        'finalizado_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date:Y-m-d',
            'numero' => 'integer',
            'llamado_at' => 'datetime',
            'atendido_at' => 'datetime',
            'finalizado_at' => 'datetime',
        ];
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function ciudadano(): BelongsTo
    {
        return $this->belongsTo(Ciudadano::class);
    }

    public function ventanilla(): BelongsTo
    {
        return $this->belongsTo(Ventanilla::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialTurno::class);
    }

    public function scopeDelDia(Builder $query, ?string $fecha = null): Builder
    {
        return $query->where('fecha', $fecha ?? today()->toDateString());
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeLlamados(Builder $query): Builder
    {
        return $query->whereNotNull('llamado_at');
    }

    public function scopeAtendidos(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ATENDIDO);
    }

    public function estadoEtiqueta(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'En espera',
            self::ESTADO_LLAMANDO => 'Llamando',
            self::ESTADO_ATENDIDO => 'Atendido',
            self::ESTADO_AUSENTE => 'Ausente',
            self::ESTADO_CANCELADO => 'Cancelado',
            default => ucfirst($this->estado),
        };
    }

    public function estadoClase(): string
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'text-bg-warning',
            self::ESTADO_LLAMANDO => 'text-bg-primary',
            self::ESTADO_ATENDIDO => 'text-bg-success',
            self::ESTADO_AUSENTE => 'text-bg-secondary',
            self::ESTADO_CANCELADO => 'text-bg-danger',
            default => 'text-bg-light',
        };
    }

    /**
     * Posición dentro de la cola del área. Solo aplica a turnos en espera.
     */
    public function posicionEnCola(): ?int
    {
        if ($this->estado !== self::ESTADO_PENDIENTE) {
            return null;
        }

        $antes = static::query()
            ->where('fecha', $this->fecha->toDateString())
            ->where('area_id', $this->area_id)
            ->where('estado', self::ESTADO_PENDIENTE)
            ->where(function (Builder $query) {
                $query->where('created_at', '<', $this->created_at)
                    ->orWhere(function (Builder $query) {
                        $query->where('created_at', $this->created_at)
                            ->where('id', '<', $this->id);
                    });
            })
            ->count();

        return $antes + 1;
    }

    public function esperaSegundos(): ?int
    {
        if (! $this->llamado_at) {
            return null;
        }

        return (int) $this->created_at->diffInSeconds($this->llamado_at);
    }

    /**
     * Datos públicos del turno, sin información personal del ciudadano.
     *
     * @return array<string, mixed>
     */
    public function resumen(): array
    {
        $this->loadMissing(['servicio', 'area', 'ventanilla']);

        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'numero' => $this->numero,
            'estado' => $this->estado,
            'fecha' => $this->fecha?->toDateString(),
            'servicio' => [
                'nombre' => $this->servicio?->nombre,
            ],
            'area' => [
                'nombre' => $this->area?->nombre,
            ],
            'ventanilla' => $this->ventanilla ? [
                'codigo' => $this->ventanilla->codigo,
                'nombre' => $this->ventanilla->nombre,
            ] : null,
            'llamado_at' => $this->llamado_at?->toIso8601String(),
        ];
    }
}
