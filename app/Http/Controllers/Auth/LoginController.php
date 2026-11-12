<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if ($user = Auth::user()) {
            return redirect()->to($user->rutaPanel());
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'correo electrónico',
            'password' => 'contraseña',
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $segundos = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos fallidos. Vuelve a intentarlo en {$segundos} segundos.",
            ]);
        }

        $credenciales = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'activo' => true,
        ];

        if (! Auth::attempt($credenciales, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son correctos, o la cuenta está desactivada.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        Log::info('Inicio de sesión', [
            'user_id' => $user->id,
            'rol' => $user->rol?->nombre,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended($user->rutaPanel());
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            Log::info('Cierre de sesión', ['user_id' => $user->id]);
        }

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
