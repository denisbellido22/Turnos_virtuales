@php($esEdicion = $servicio->exists)

<form method="POST" action="{{ $esEdicion ? route('admin.servicios.update', $servicio) : route('admin.servicios.store') }}">
    @csrf
    @if ($esEdicion)
        @method('PUT')
    @endif

    <div class="card app-card">
        <div class="card-header">
            <h3 class="card-title">Datos del servicio</h3>
            <p class="card-subtitle">El prefijo forma parte del código del turno, por ejemplo INF-001.</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="area_id" class="form-label">Área de atención</label>
                    <select id="area_id" name="area_id" required class="form-select @error('area_id') is-invalid @enderror">
                        <option value="">Seleccionar área…</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected((string) old('area_id', $servicio->area_id) === (string) $area->id)>
                                {{ $area->nombre }} {{ $area->activo ? '' : '(inactiva)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label for="prefijo" class="form-label">Prefijo</label>
                    <input id="prefijo" type="text" name="prefijo" value="{{ old('prefijo', $servicio->prefijo) }}" required
                           maxlength="5" class="form-control text-uppercase @error('prefijo') is-invalid @enderror"
                           placeholder="INF" style="text-transform: uppercase">
                    @error('prefijo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">2 a 5 letras o números, único.</div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
                               @checked(old('activo', $servicio->activo ?? true))>
                        <label class="form-check-label" for="activo">Servicio activo</label>
                    </div>
                </div>

                <div class="col-12">
                    <label for="nombre" class="form-label">Nombre del servicio</label>
                    <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $servicio->nombre) }}" required
                           class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Licencia de conducir">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              placeholder="Requisitos o alcance del servicio.">{{ old('descripcion', $servicio->descripcion) }}</textarea>
                    @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.servicios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-brand">
            <i class="bi bi-check2 me-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Crear servicio' }}
        </button>
    </div>
</form>
