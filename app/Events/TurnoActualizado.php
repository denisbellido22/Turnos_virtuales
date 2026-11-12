<?php

namespace App\Events;

use App\Models\Turno;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnoActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Turno $turno)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('turnos');
    }

    public function broadcastAs(): string
    {
        return 'turno.actualizado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['turno' => $this->turno->resumen()];
    }
}
