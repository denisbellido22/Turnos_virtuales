@php($esEdicion = $ventanilla->exists)

<form method="POST" action="{{ $esEdicion ? route('admin.ventanillas.update', $ventanilla) : route('admin.ventanillas.store') }}">
    @csrf
    @if ($esEdicion)
        @method('PUT')
    @endif

    <div class="card app-card">
        <div class="card-header">
            <h3 class="card-title">Datos de la ventanilla</h3>
            <p class="card-subtitle">El código aparece en el panel de atención y en la pantalla pública.</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="area_id" class="form-label">Área de atención</label>
                    <select id="area_id" name="area_id" required class="form-select @error('area_id') is-invalid @enderror">
                        <option value="">Seleccionar área…</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected((string) old('area_id', $ventanilla->area_id) === (string) $area->id)>
                                {{ $area->nombre }} {{ $area->activo ? '' : '(inactiva)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="codigo" class="form-label">Código</label>
                    <input id="codigo" type="text" name="codigo" value="{{ old('codigo', $ventanilla->codigo) }}" required
                           class="form-control text-uppercase @error('codigo') is-invalid @enderror" placeholder="V-01"
                           style="text-transform: uppercase">
                    @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Único en todo el sistema.</div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
                               @checked(old('activo', $ventanilla->activo ?? true))>
                        <label class="form-check-label" for="activo">Ventanilla activa</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $ventanilla->nombre) }}" required
                           class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Caja única">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.ventanillas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-brand">
            <i class="bi bi-check2 me-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Crear ventanilla' }}
        </button>
    </div>
</form>
