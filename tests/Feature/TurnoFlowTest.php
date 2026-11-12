<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Ciudadano;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\User;
use App\Models\Ventanilla;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TurnoFlowTest extends TestCase
{
    use RefreshDatabase;

    private Area $area;

    private Servicio $servicio;

    private Ventanilla $ventanilla;

    private User $empleado;

    private User $ciudadanoUser;

    private Ciudadano $ciudadano;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
        $this->seed(RolesSeeder::class);

        $this->area = Area::factory()->create(['nombre' => 'Área de pruebas']);
        $this->servicio = Servicio::factory()->create([
            'area_id' => $this->area->id,
            'nombre' => 'Servicio de pruebas',
            'prefijo' => 'TST',
        ]);
        $this->ventanilla = Ventanilla::factory()->create([
            'area_id' => $this->area->id,
            'codigo' => 'V-99',
        ]);
        $this->empleado = User::factory()->empleado()->create([
            'area_id' => $this->area->id,
            'ventanilla_id' => $this->ventanilla->id,
        ]);
        $this->ciudadanoUser = User::factory()->create();
        $this->ciudadano = Ciudadano::factory()->create(['user_id' => $this->ciudadanoUser->id]);
    }

    public function test_los_turnos_se_generan_de_forma_consecutiva_y_auditada(): void
    {
        $this->actingAs($this->ciudadanoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id])
            ->assertRedirect();

        $primerTurno = Turno::query()->firstOrFail();

        $this->assertSame('TST-001', $primerTurno->codigo);
        $this->assertSame(1, $primerTurno->numero);
        $this->assertSame(Turno::ESTADO_PENDIENTE, $primerTurno->estado);
        $this->assertSame($this->area->id, $primerTurno->area_id);
        $this->assertDatabaseHas('historial_turnos', [
            'turno_id' => $primerTurno->id,
            'estado_nuevo' => Turno::ESTADO_PENDIENTE,
            'accion' => 'crear',
        ]);

        $segundoUser = User::factory()->create();
        Ciudadano::factory()->create(['user_id' => $segundoUser->id]);

        $this->actingAs($segundoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id])
            ->assertRedirect();

        $this->assertDatabaseHas('turnos', ['codigo' => 'TST-002', 'numero' => 2]);
    }

    public function test_un_ciudadano_no_puede_tener_dos_turnos_activos_el_mismo_dia(): void
    {
        $this->actingAs($this->ciudadanoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id]);

        $this->actingAs($this->ciudadanoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id])
            ->assertSessionHasErrors('servicio_id');

        $this->assertSame(1, Turno::query()->count());
    }

    public function test_el_empleado_llama_al_siguiente_turno_y_lo_marca_atendido(): void
    {
        $this->actingAs($this->ciudadanoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id]);

        $turno = Turno::query()->firstOrFail();

        $this->actingAs($this->empleado)
            ->post(route('atencion.turnos.llamar'))
            ->assertRedirect();

        $turno->refresh();
        $this->assertSame(Turno::ESTADO_LLAMANDO, $turno->estado);
        $this->assertSame($this->ventanilla->id, $turno->ventanilla_id);
        $this->assertSame($this->empleado->id, $turno->user_id);
        $this->assertNotNull($turno->llamado_at);

        $this->actingAs($this->empleado)
            ->post(route('atencion.turnos.atender', $turno))
            ->assertRedirect();

        $turno->refresh();
        $this->assertSame(Turno::ESTADO_ATENDIDO, $turno->estado);
        $this->assertNotNull($turno->atendido_at);
        $this->assertNotNull($turno->finalizado_at);
        $this->assertDatabaseHas('historial_turnos', [
            'turno_id' => $turno->id,
            'accion' => 'atender',
            'estado_nuevo' => Turno::ESTADO_ATENDIDO,
        ]);
    }

    public function test_un_empleado_no_puede_operar_un_turno_de_otra_ventanilla(): void
    {
        $this->actingAs($this->ciudadanoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id]);

        $turno = Turno::query()->firstOrFail();

        $this->actingAs($this->empleado)
            ->post(route('atencion.turnos.llamar'));

        $otraVentanilla = Ventanilla::factory()->create(['area_id' => $this->area->id]);
        $otroEmpleado = User::factory()->empleado()->create([
            'area_id' => $this->area->id,
            'ventanilla_id' => $otraVentanilla->id,
        ]);

        $this->actingAs($otroEmpleado)
            ->post(route('atencion.turnos.atender', $turno))
            ->assertForbidden();
    }

    public function test_el_repetir_llamado_actualiza_la_marca_de_tiempo(): void
    {
        $this->actingAs($this->ciudadanoUser)
            ->post(route('ciudadano.turnos.store'), ['servicio_id' => $this->servicio->id]);

        $turno = Turno::query()->firstOrFail();
        $this->actingAs($this->empleado)->post(route('atencion.turnos.llamar'));
        $turno->refresh();
        $primeraLlamada = $turno->llamado_at->copy();

        $this->travel(2)->minutes();

        $this->actingAs($this->empleado)
            ->post(route('atencion.turnos.repetir', $turno))
            ->assertRedirect();

        $turno->refresh();
        $this->assertTrue($turno->llamado_at->greaterThan($primeraLlamada));
        $this->assertDatabaseHas('historial_turnos', [
            'turno_id' => $turno->id,
            'accion' => 'repetir_llamado',
        ]);
    }
}
