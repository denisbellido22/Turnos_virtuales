<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_paginas_principales_renderizan_con_datos_reales(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@turnos.test')->firstOrFail();
        $empleado = User::query()->where('email', 'maria.lopez@turnos.test')->firstOrFail();
        $ciudadano = User::query()->where('email', 'ciudadano@turnos.test')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Cola por área');
        $this->actingAs($admin)->get(route('admin.usuarios.index'))->assertOk()->assertSee('Usuarios y empleados');
        $this->actingAs($admin)->get(route('admin.areas.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.servicios.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.ventanillas.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reportes.index'))->assertOk();

        $this->actingAs($empleado)->get(route('atencion.panel'))->assertOk()->assertSee('Cola del área');
        $this->actingAs($ciudadano)->get(route('ciudadano.turnos.index'))->assertOk()->assertSee('Mis turnos');

        $this->get(route('pantalla.index'))->assertOk()->assertSee('Ahora atendiendo');
    }
}
