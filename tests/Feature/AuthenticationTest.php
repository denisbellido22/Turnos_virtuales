<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_un_ciudadano_puede_registrarse_y_accede_a_sus_turnos(): void
    {
        $respuesta = $this->post('/registro', [
            'name' => 'María Ciudadana',
            'email' => 'maria@ciudadana.test',
            'documento' => '87654321',
            'telefono' => '70012345',
            'direccion' => 'Av. Central 123',
            'password' => 'ClaveSegura123!',
            'password_confirmation' => 'ClaveSegura123!',
        ]);

        $respuesta->assertRedirect(route('ciudadano.turnos.index'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', ['email' => 'maria@ciudadana.test']);
        $this->assertDatabaseHas('ciudadanos', ['documento' => '87654321']);
    }

    public function test_un_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $usuario = User::factory()->inactivo()->create([
            'email' => 'inactivo@turnos.test',
            'password' => 'Password123!',
        ]);

        $respuesta = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'Password123!',
        ]);

        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_el_login_redirige_al_panel_correspondiente_al_rol(): void
    {
        $admin = User::factory()->administrador()->create([
            'email' => 'admin-login@turnos.test',
            'password' => 'Password123!',
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'Password123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_un_ciudadano_no_puede_acceder_al_panel_administrativo(): void
    {
        $ciudadano = User::factory()->create();

        $this->actingAs($ciudadano)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_un_empleado_no_puede_acceder_a_reportes_administrativos(): void
    {
        $empleado = User::factory()->empleado()->create();

        $this->actingAs($empleado)
            ->get(route('admin.reportes.index'))
            ->assertForbidden();
    }
}
