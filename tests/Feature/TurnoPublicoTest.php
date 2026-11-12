<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Ciudadano;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TurnoPublicoTest extends TestCase
{
    use RefreshDatabase;

    private Servicio $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
        $this->seed(RolesSeeder::class);

        $area = Area::factory()->create(['nombre' => 'Atención general']);
        $this->servicio = Servicio::factory()->create([
            'area_id' => $area->id,
            'nombre' => 'Trámite general',
            'prefijo' => 'GEN',
        ]);
    }

    public function test_un_invitado_obtiene_turno_solo_con_sus_datos(): void
    {
        $this->get(route('turnos.create'))->assertOk()->assertSee('Nombre completo');

        $respuesta = $this->post(route('turnos.store'), [
            'nombre' => 'Ana Pérez',
            'documento' => '12345678',
            'telefono' => '71234567',
            'servicio_id' => $this->servicio->id,
        ]);

        $turno = Turno::query()->firstOrFail();

        $respuesta->assertRedirect(route('turnos.show', $turno->codigo));
        $this->assertSame('GEN-001', $turno->codigo);
        $this->assertSame(Turno::ESTADO_PENDIENTE, $turno->estado);

        $ciudadano = Ciudadano::query()->findOrFail($turno->ciudadano_id);
        $this->assertSame('Ana Pérez', $ciudadano->nombre);
        $this->assertSame('12345678', $ciudadano->documento);
        $this->assertNull($ciudadano->user_id);

        $this->get(route('turnos.show', $turno->codigo))
            ->assertOk()
            ->assertSee('GEN-001')
            ->assertDontSee('12345678');

        $this->getJson(route('turnos.estado', $turno->codigo))
            ->assertOk()
            ->assertJsonPath('estado', Turno::ESTADO_PENDIENTE)
            ->assertJsonPath('posicion', 1);
    }

    public function test_el_mismo_documento_no_acumula_turnos_activos(): void
    {
        $datos = [
            'nombre' => 'Ana Pérez',
            'documento' => '12345678',
            'servicio_id' => $this->servicio->id,
        ];

        $this->post(route('turnos.store'), $datos)->assertRedirect();

        $this->post(route('turnos.store'), $datos)->assertSessionHasErrors('servicio_id');

        $this->assertSame(1, Turno::query()->count());
        $this->assertSame(1, Ciudadano::query()->count());
    }

    public function test_un_usuario_registrado_sigue_solicitando_su_turno_con_su_cuenta(): void
    {
        $usuario = User::factory()->create();
        Ciudadano::factory()->create(['user_id' => $usuario->id, 'documento' => '87654321']);

        $this->actingAs($usuario)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id])
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', ['codigo' => 'GEN-001']);
    }
}
