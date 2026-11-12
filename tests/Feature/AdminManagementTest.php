<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Rol;
use App\Models\User;
use App\Models\Ventanilla;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->admin = User::factory()->administrador()->create();
    }

    public function test_el_administrador_puede_crear_un_area(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.areas.store'), [
                'nombre' => 'Nueva área',
                'descripcion' => 'Atención de trámites nuevos.',
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.areas.index'));

        $this->assertDatabaseHas('areas', ['nombre' => 'Nueva área', 'activo' => true]);
    }

    public function test_el_administrador_puede_crear_un_empleado_con_ventanilla(): void
    {
        $area = Area::factory()->create();
        $ventanilla = Ventanilla::factory()->create(['area_id' => $area->id]);
        $rolEmpleado = Rol::query()->where('nombre', Rol::EMPLEADO)->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Empleado Nuevo',
                'email' => 'empleado.nuevo@turnos.test',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'rol_id' => $rolEmpleado->id,
                'area_id' => $area->id,
                'ventanilla_id' => $ventanilla->id,
                'activo' => '1',
            ])
            ->assertRedirect(route('admin.usuarios.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'empleado.nuevo@turnos.test',
            'area_id' => $area->id,
            'ventanilla_id' => $ventanilla->id,
            'activo' => true,
        ]);
    }

    public function test_no_se_puede_asignar_una_ventanilla_de_otra_area(): void
    {
        $area = Area::factory()->create();
        $otraArea = Area::factory()->create();
        $ventanilla = Ventanilla::factory()->create(['area_id' => $otraArea->id]);
        $rolEmpleado = Rol::query()->where('nombre', Rol::EMPLEADO)->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.usuarios.store'), [
                'name' => 'Empleado Incorrecto',
                'email' => 'empleado.incorrecto@turnos.test',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'rol_id' => $rolEmpleado->id,
                'area_id' => $area->id,
                'ventanilla_id' => $ventanilla->id,
                'activo' => '1',
            ])
            ->assertSessionHasErrors('ventanilla_id');

        $this->assertDatabaseMissing('users', ['email' => 'empleado.incorrecto@turnos.test']);
    }

    public function test_un_empleado_no_puede_gestionar_areas(): void
    {
        $empleado = User::factory()->empleado()->create();

        $this->actingAs($empleado)
            ->get(route('admin.areas.index'))
            ->assertForbidden();
    }
}
