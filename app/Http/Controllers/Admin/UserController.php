<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Area;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $usuarios = User::query()
            ->with(['rol', 'area', 'ventanilla'])
            ->whereHas('rol', fn ($query) => $query->whereIn('nombre', [Rol::ADMINISTRADOR, Rol::EMPLEADO]))
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('name', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalAdministradores = User::query()
            ->whereHas('rol', fn ($query) => $query->where('nombre', Rol::ADMINISTRADOR))
            ->count();

        $totalEmpleados = User::query()
            ->whereHas('rol', fn ($query) => $query->where('nombre', Rol::EMPLEADO))
            ->count();

        $totalActivos = User::query()
            ->where('activo', true)
            ->whereHas('rol', fn ($query) => $query->whereIn('nombre', [Rol::ADMINISTRADOR, Rol::EMPLEADO]))
            ->count();

        return view('admin.usuarios.index', compact(
            'usuarios',
            'buscar',
            'totalAdministradores',
            'totalEmpleados',
            'totalActivos',
        ));
    }

    public function create(): View
    {
        return view('admin.usuarios.create', [
            'usuario' => new User(['activo' => true]),
            'roles' => $this->rolesPermitidos(),
            'areas' => $this->areasConVentanillas(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $rol = Rol::query()->findOrFail($datos['rol_id']);
        $esEmpleado = $rol->nombre === Rol::EMPLEADO;

        $usuario = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol_id' => $rol->id,
            'area_id' => $esEmpleado ? $datos['area_id'] : null,
            'ventanilla_id' => $esEmpleado ? $datos['ventanilla_id'] : null,
            'activo' => $datos['activo'] ?? true,
        ]);

        Log::info('Usuario interno creado', [
            'user_id' => $usuario->id,
            'rol' => $rol->nombre,
            'creado_por' => $request->user()->id,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} creado correctamente.");
    }

    public function edit(User $usuario): View
    {
        abort_unless($usuario->esAdministrador() || $usuario->esEmpleado(), 404);

        return view('admin.usuarios.edit', [
            'usuario' => $usuario->load(['rol', 'area', 'ventanilla']),
            'roles' => $this->rolesPermitidos(),
            'areas' => $this->areasConVentanillas(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $usuario): RedirectResponse
    {
        abort_unless($usuario->esAdministrador() || $usuario->esEmpleado(), 404);

        $datos = $request->validated();
        $rol = Rol::query()->findOrFail($datos['rol_id']);
        $esEmpleado = $rol->nombre === Rol::EMPLEADO;

        $usuario->fill([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'rol_id' => $rol->id,
            'area_id' => $esEmpleado ? $datos['area_id'] : null,
            'ventanilla_id' => $esEmpleado ? $datos['ventanilla_id'] : null,
            'activo' => $datos['activo'] ?? true,
        ]);

        if (! empty($datos['password'])) {
            $usuario->password = $datos['password'];
        }

        $usuario->save();

        Log::info('Usuario interno actualizado', [
            'user_id' => $usuario->id,
            'actualizado_por' => $request->user()->id,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} actualizado correctamente.");
    }

    public function toggleActivo(Request $request, User $usuario): RedirectResponse
    {
        abort_unless($usuario->esAdministrador() || $usuario->esEmpleado(), 404);

        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        Log::info('Estado de usuario modificado', [
            'user_id' => $usuario->id,
            'activo' => $usuario->activo,
            'modificado_por' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            $usuario->activo
                ? "Usuario {$usuario->name} activado."
                : "Usuario {$usuario->name} desactivado."
        );
    }

    private function rolesPermitidos()
    {
        return Rol::query()
            ->whereIn('nombre', [Rol::ADMINISTRADOR, Rol::EMPLEADO])
            ->orderBy('nombre')
            ->get();
    }

    private function areasConVentanillas()
    {
        return Area::query()
            ->with(['ventanillas' => fn ($query) => $query->orderBy('codigo')])
            ->orderBy('nombre')
            ->get();
    }
}
