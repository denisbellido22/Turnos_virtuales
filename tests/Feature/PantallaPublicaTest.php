<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Ciudadano;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use App\Models\Ventanilla;
use App\Models\Ventanilla as VentanillaModel;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PantallaPublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pantalla_muestra_el_ultimo_turno_llamado_sin_datos_personales(): void
    {
        Event::fake();
        $this->seed(RolesSeeder::class);

        $area = Area::factory()->create(['nombre' => 'Atención general']);
        $servicio = Servicio::factory()->create(['area_id' => $area->id, 'nombre' => 'Trámite general', 'prefijo' => 'GEN']);
        $ventanilla = VentanillaModel::factory()->create(['area_id' => $area->id, 'codigo' => 'V-12']);
        $empleado = User::factory()->empleado()->create([
            'area_id' => $area->id,
            'ventanilla_id' => $ventanilla->id,
        ]);
        $ciudadanoUser = User::factory()->create(['name' => 'Persona Reservada']);
        $ciudadano = Ciudadano::factory()->create([
            'user_id' => $ciudadanoUser->id,
            'documento' => '99999999',
        ]);

        $this->actingAs($ciudadanoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $servicio->id]);
        $turno = Turno::query()->firstOrFail();
        $this->actingAs($empleado)->post(route('atencion.turnos.llamar'));

        $this->get(route('pantalla.index'))
            ->assertOk()
            ->assertSee($turno->codigo)
            ->assertSee('V-12')
            ->assertDontSee('Persona Reservada')
            ->assertDontSee('99999999');

        $this->getJson(route('pantalla.datos'))
            ->assertOk()
            ->assertJsonPath('llamados.0.codigo', $turno->codigo)
            ->assertJsonPath('llamados.0.ventanilla.codigo', 'V-12')
            ->assertJsonMissing(['documento' => '99999999']);
    }
}
