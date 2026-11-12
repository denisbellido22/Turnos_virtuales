@props([
    'titulo' => '',
    'valor' => '0',
    'icono' => 'bi-bar-chart',
    'color' => 'primary',
    'detalle' => null,
])

<div class="card stat-card border-0 shadow-sm h-100">
    <div class="card-body d-flex justify-content-between align-items-start gap-3">
        <div class="min-w-0">
            <p class="stat-label">{{ $titulo }}</p>
            <div class="stat-value">{{ $valor }}</div>
            @if ($detalle)
                <p class="stat-detail">{{ $detalle }}</p>
            @endif
        </div>
        <span class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}-emphasis">
            <i class="bi {{ $icono }}"></i>
        </span>
    </div>
</div>
