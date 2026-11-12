<?php

use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\ServicioController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VentanillaController;
use App\Http\Controllers\Atencion\PanelController;
use App\Http\Controllers\Atencion\TurnoAccionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Ciudadano\TurnoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PantallaController;
use App\Http\Controllers\TurnoPublicoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
    Route::get('registro', [RegisterController::class, 'create'])->name('register');
    Route::post('registro', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Solicitud y seguimiento de turnos sin necesidad de cuenta.
Route::get('turno', [TurnoPublicoController::class, 'create'])->name('turnos.create');
Route::post('turno', [TurnoPublicoController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('turnos.store');
Route::get('turno/buscar', [TurnoPublicoController::class, 'buscar'])->name('turnos.buscar');
Route::get('turno/{codigo}', [TurnoPublicoController::class, 'show'])->name('turnos.show');
Route::get('turno/{codigo}/estado', [TurnoPublicoController::class, 'estado'])->name('turnos.estado');

Route::get('pantalla', [PantallaController::class, 'index'])->name('pantalla.index');
Route::get('pantalla/datos', [PantallaController::class, 'datos'])->name('pantalla.datos');

Route::middleware(['auth', 'activo', 'role:ciudadano'])
    ->prefix('mis-turnos')
    ->name('ciudadano.')
    ->group(function () {
        Route::get('/', [TurnoController::class, 'index'])->name('turnos.index');
        Route::post('/', [TurnoController::class, 'store'])->name('turnos.store');
        Route::get('/{turno}', [TurnoController::class, 'show'])->name('turnos.show');
        Route::get('/{turno}/estado', [TurnoController::class, 'estado'])->name('turnos.estado');
    });

Route::middleware(['auth', 'activo', 'role:empleado'])
    ->prefix('atencion')
    ->name('atencion.')
    ->group(function () {
        Route::get('/', [PanelController::class, 'index'])->name('panel');
        Route::get('/cola', [PanelController::class, 'cola'])->name('cola');
        Route::post('/turnos/llamar', [TurnoAccionController::class, 'llamarSiguiente'])->name('turnos.llamar');
        Route::post('/turnos/{turno}/repetir', [TurnoAccionController::class, 'repetir'])->name('turnos.repetir');
        Route::post('/turnos/{turno}/atender', [TurnoAccionController::class, 'atender'])->name('turnos.atender');
        Route::post('/turnos/{turno}/ausente', [TurnoAccionController::class, 'ausente'])->name('turnos.ausente');
        Route::post('/turnos/{turno}/cancelar', [TurnoAccionController::class, 'cancelar'])->name('turnos.cancelar');

        // Acciones por audio
        Route::post('/turnos/{turno}/atender-audio', [TurnoAccionController::class, 'atenderAudio'])->name('turnos.atender_audio');
        Route::post('/turnos/{turno}/llamar-audio', [TurnoAccionController::class, 'llamarAudio'])->name('turnos.llamar_audio');
        Route::post('/turnos/{turno}/repetir-audio', [TurnoAccionController::class, 'repetirAudio'])->name('turnos.repetir_audio');
    });

Route::middleware(['auth', 'activo', 'role:administrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('reportes', ReporteController::class)->name('reportes.index');

        Route::resource('usuarios', UserController::class)
            ->parameters(['usuarios' => 'usuario'])
            ->except(['show', 'destroy']);
        Route::patch('usuarios/{usuario}/estado', [UserController::class, 'toggleActivo'])->name('usuarios.estado');

        Route::resource('areas', AreaController::class)->except('show');
        Route::resource('servicios', ServicioController::class)->except('show');
        Route::resource('ventanillas', VentanillaController::class)->except('show');
    });
