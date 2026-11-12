@php($esEdicion = $area->exists)

<form method="POST" action="{{ $esEdicion ? route('admin.areas.update', $area) : route('admin.areas.store') }}">
    @csrf
    @if ($esEdicion)
        @method('PUT')
    @endif

    <div class="card app-card">
        <div class="card-header">
            <h3 class="card-title">Datos del área</h3>
            <p class="card-subtitle">El nombre se mostrará a los ciudadanos al solicitar un turno.</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre del área</label>
                    <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $area->nombre) }}" required
                           class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Atención al Ciudadano">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
                               @checked(old('activo', $area->activo ?? true))>
                        <label class="form-check-label" for="activo">Área activa</label>
                    </div>
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              placeholder="Describe brevemente los trámites que atiende esta área.">{{ old('descripcion', $area->descripcion) }}</textarea>
                    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.areas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-brand">
            <i class="bi bi-check2 me-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Crear área' }}
        </button>
    </div>
</form>
