@php
    $esEdicion = $usuario->exists;
    $rolSeleccionado = old('rol_id', $usuario->rol_id);
    $areaSeleccionada = old('area_id', $usuario->area_id);
    $ventanillaSeleccionada = old('ventanilla_id', $usuario->ventanilla_id);
@endphp

<form id="usuario-form" method="POST"
      action="{{ $esEdicion ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}">
    @csrf
    @if ($esEdicion)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card app-card">
                <div class="card-header">
                    <h3 class="card-title">Datos de la cuenta</h3>
                    <p class="card-subtitle">Información de acceso del personal.</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre completo</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                                   class="form-control @error('name') is-invalid @enderror" placeholder="Nombres y apellidos">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                                   class="form-control @error('email') is-invalid @enderror" placeholder="nombre@institucion.gob">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Contraseña @if ($esEdicion)<span class="text-muted fw-normal">(opcional)</span>@endif
                            </label>
                            <input id="password" type="password" name="password" @required(! $esEdicion) autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror" placeholder="Mínimo 8 caracteres">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($esEdicion)<div class="form-text">Déjala vacía para conservar la contraseña actual.</div>@endif
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                                   class="form-control" placeholder="Repite la contraseña">
                        </div>

                        <div class="col-md-6">
                            <label for="rol_id" class="form-label">Rol</label>
                            <select id="rol_id" name="rol_id" required class="form-select @error('rol_id') is-invalid @enderror">
                                <option value="">Seleccionar rol…</option>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected((string) $rolSeleccionado === (string) $rol->id)>
                                        {{ ucfirst($rol->nombre) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rol_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1"
                                       @checked(old('activo', $usuario->activo ?? true))>
                                <label class="form-check-label" for="activo">Cuenta activa</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card app-card">
                <div class="card-header">
                    <h3 class="card-title">Puesto de atención</h3>
                    <p class="card-subtitle">Solo aplica a empleados.</p>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="area_id" class="form-label">Área</label>
                        <select id="area_id" name="area_id" class="form-select @error('area_id') is-invalid @enderror">
                            <option value="">Sin área</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}" @selected((string) $areaSeleccionada === (string) $area->id)>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="ventanilla_id" class="form-label">Ventanilla</label>
                        <select id="ventanilla_id" name="ventanilla_id" class="form-select @error('ventanilla_id') is-invalid @enderror">
                            <option value="">Sin ventanilla</option>
                            @foreach ($areas as $area)
                                @foreach ($area->ventanillas as $ventanilla)
                                    <option value="{{ $ventanilla->id }}" data-area="{{ $area->id }}"
                                        @selected((string) $ventanillaSeleccionada === (string) $ventanilla->id)>
                                        {{ $ventanilla->codigo }} · {{ $ventanilla->nombre }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        @error('ventanilla_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="alert alert-light border small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Un empleado necesita área y ventanilla para llamar turnos. Su cola corresponde al área asignada.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-brand">
            <i class="bi bi-check2 me-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
    </div>
</form>
