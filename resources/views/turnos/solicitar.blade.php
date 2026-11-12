@extends('layouts.guest')

@section('titulo', 'Solicitar turno')

@section('contenido')
    <span class="eyebrow">Sin cuenta, sin filas</span>
    <h1 class="h3 mb-2">Obtén tu turno</h1>
    <p class="text-muted mb-4">Completa tus datos y elige el servicio. No necesitas crear una cuenta.</p>

    @include('partials.flash')

    @if ($servicios->isEmpty())
        <div class="empty-state compact">
            <i class="bi bi-inbox"></i>
            <p>No hay servicios disponibles en este momento.</p>
        </div>
    @else
        <form method="POST" action="{{ route('turnos.store') }}">
            @csrf

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="120"
                       autocomplete="name"
                       class="form-control @error('nombre') is-invalid @enderror">
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="documento" class="form-label">Documento de identidad</label>
                    <input type="text" id="documento" name="documento" value="{{ old('documento') }}" required maxlength="30"
                           inputmode="numeric" autocomplete="off"
                           class="form-control @error('documento') is-invalid @enderror">
                    @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label for="telefono" class="form-label">Teléfono <span class="text-muted">(opcional)</span></label>
                    <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" maxlength="30"
                           autocomplete="tel"
                           class="form-control @error('telefono') is-invalid @enderror">
                    @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="servicio_id" class="form-label">Servicio</label>
                <select id="servicio_id" name="servicio_id" required
                        class="form-select @error('servicio_id') is-invalid @enderror">
                    <option value="">Selecciona un servicio…</option>
                    @foreach ($servicios as $areaNombre => $items)
                        <optgroup label="{{ $areaNombre }}">
                            @foreach ($items as $servicio)
                                <option value="{{ $servicio->id }}" @selected((string) old('servicio_id') === (string) $servicio->id)>
                                    {{ $servicio->nombre }} · {{ $areaNombre }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('servicio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-brand btn-lg w-100">
                <i class="bi bi-ticket-perforated me-2"></i> Generar mi turno
            </button>
        </form>
    @endif

    <p class="small text-muted mt-3 mb-0">
        Tu documento se usa solo para evitar que una misma persona tenga dos turnos activos el mismo día.
    </p>
@endsection
