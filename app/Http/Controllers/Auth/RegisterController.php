<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Ciudadano;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(Auth::user()->rutaPanel());
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'documento' => ['required', 'string', 'max:30', 'unique:ciudadanos,documento'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [], [
            'name' => 'nombre completo',
            'email' => 'correo electrónico',
            'documento' => 'documento de identidad',
            'telefono' => 'teléfono',
            'direccion' => 'dirección',
            'password' => 'contraseña',
        ]);

        $user = DB::transaction(function () use ($datos) {
            $rol = Rol::query()->where('nombre', Rol::CIUDADANO)->firstOrFail();

            $user = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => $datos['password'],
                'rol_id' => $rol->id,
                'activo' => true,
            ]);

            Ciudadano::create([
                'user_id' => $user->id,
                'documento' => $datos['documento'],
                'telefono' => $datos['telefono'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        Log::info('Registro de ciudadano', [
            'user_id' => $user->id,
            'documento' => $datos['documento'],
        ]);

        return redirect()->route('ciudadano.turnos.index')
            ->with('success', 'Tu cuenta fue creada. Ya puedes solicitar un turno.');
    }
}
