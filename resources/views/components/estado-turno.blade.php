@props(['estado'])

@php
    $mapa = [
        \App\Models\Turno::ESTADO_PENDIENTE => ['En espera', 'text-bg-warning'],
        \App\Models\Turno::ESTADO_LLAMANDO => ['Llamando', 'text-bg-primary'],
        \App\Models\Turno::ESTADO_ATENDIDO => ['Atendido', 'text-bg-success'],
        \App\Models\Turno::ESTADO_AUSENTE => ['Ausente', 'text-bg-secondary'],
        \App\Models\Turno::ESTADO_CANCELADO => ['Cancelado', 'text-bg-danger'],
    ];
    [$texto, $clase] = $mapa[$estado] ?? [ucfirst($estado), 'text-bg-light'];
@endphp

<span {{ $attributes->merge(['class' => "badge rounded-pill estado-badge {$clase}"]) }}>{{ $texto }}</span>
