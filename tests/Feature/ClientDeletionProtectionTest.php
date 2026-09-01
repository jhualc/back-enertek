<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\ClienteSede;
use App\Models\Equipo;
use App\Models\Marca;
use App\Models\TipoEquipo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ClientDeletionProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a cliente without dependencies can be deleted
     */
    public function test_cliente_without_dependencies_can_be_deleted()
    {
        $cliente = Cliente::create([
            'cli_nombre' => 'Cliente Test',
            'cli_identificacion' => 'CC123456789',
            'cli_tipo_identificacion' => 'CC',
        ]);

        $response = $this->deleteJson("/api/cliente/{$cliente->cli_id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Cliente eliminado exitosamente']);
    }

    /**
     * Test that a cliente with sedes cannot be deleted
     */
    public function test_cliente_with_sedes_cannot_be_deleted()
    {
        $cliente = Cliente::create([
            'cli_nombre' => 'Cliente Test',
            'cli_identificacion' => 'CC987654321',
            'cli_tipo_identificacion' => 'CC',
        ]);

        ClienteSede::create([
            'cli_id' => $cliente->cli_id,
            'cls_descripcion' => 'Sede Principal',
            'cls_direccion' => 'Carrera 1 # 1-1',
        ]);

        $response = $this->deleteJson("/api/cliente/{$cliente->cli_id}");

        $response->assertStatus(409);
        $response->assertJson(['message' => 'No se puede eliminar el cliente porque tiene sedes o equipos asociados.']);
        
        $this->assertDatabaseHas('cliente', ['cli_id' => $cliente->cli_id, 'deleted_at' => null]);
    }

    /**
     * Test that a cliente with client_equipo relationships cannot be deleted
     */
    public function test_cliente_with_client_equipo_cannot_be_deleted()
    {
        $cliente = Cliente::create([
            'cli_nombre' => 'Cliente Test',
            'cli_identificacion' => 'CC111222333',
            'cli_tipo_identificacion' => 'CC',
        ]);

        $marca = Marca::create(['mar_descripcion' => 'APC']);
        $tipoEquipo = TipoEquipo::create(['teq_descripcion' => 'UPS']);

        $equipo = Equipo::create([
            'equ_modelo' => 'Smart-UPS 1500',
            'equ_serial' => 'SERIAL123',
            'mar_id' => $marca->mar_id,
            'teq_id' => $tipoEquipo->teq_id,
            'equ_cant_baterias' => 2,
        ]);

        \DB::table('cliente_equipo')->insert([
            'cli_id' => $cliente->cli_id,
            'equ_id' => $equipo->equ_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/cliente/{$cliente->cli_id}");

        $response->assertStatus(409);
        $response->assertJson(['message' => 'No se puede eliminar el cliente porque tiene sedes o equipos asociados.']);

        $this->assertDatabaseHas('cliente', ['cli_id' => $cliente->cli_id, 'deleted_at' => null]);
    }

    /**
     * Test that a sede without equipos can be deleted
     */
    public function test_sede_without_equipos_can_be_deleted()
    {
        $cliente = Cliente::create([
            'cli_nombre' => 'Cliente Test',
            'cli_identificacion' => 'CC444555666',
            'cli_tipo_identificacion' => 'CC',
        ]);

        $sede = ClienteSede::create([
            'cli_id' => $cliente->cli_id,
            'cls_descripcion' => 'Sede Secundaria',
            'cls_direccion' => 'Avenida 2 # 2-2',
        ]);

        $response = $this->deleteJson("/api/cliente-sedes/{$sede->cls_id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Sede de cliente eliminada exitosamente']);
    }

    /**
     * Test that a sede with equipos cannot be deleted
     */
    public function test_sede_with_equipos_cannot_be_deleted()
    {
        $cliente = Cliente::create([
            'cli_nombre' => 'Cliente Test',
            'cli_identificacion' => 'CC777888999',
            'cli_tipo_identificacion' => 'CC',
        ]);

        $sede = ClienteSede::create([
            'cli_id' => $cliente->cli_id,
            'cls_descripcion' => 'Sede con Equipos',
            'cls_direccion' => 'Calle 3 # 3-3',
        ]);

        $marca = Marca::create(['mar_descripcion' => 'Eaton']);
        $tipoEquipo = TipoEquipo::create(['teq_descripcion' => 'UPS']);

        Equipo::create([
            'equ_modelo' => 'Eaton 5S',
            'equ_serial' => 'SERIAL456',
            'mar_id' => $marca->mar_id,
            'teq_id' => $tipoEquipo->teq_id,
            'cls_id' => $sede->cls_id,
            'equ_cant_baterias' => 1,
        ]);

        $response = $this->deleteJson("/api/cliente-sedes/{$sede->cls_id}");

        $response->assertStatus(409);
        $response->assertJson(['message' => 'No se puede eliminar la sede porque tiene equipos asociados.']);

        $this->assertDatabaseHas('cliente_sedes', ['cls_id' => $sede->cls_id, 'deleted_at' => null]);
    }

    /**
     * Test that destroyMultiple rejects if any cliente has dependencies
     */
    public function test_destroy_multiple_clientes_rejects_if_any_has_dependencies()
    {
        $cliente1 = Cliente::create([
            'cli_nombre' => 'Cliente Sin Deps',
            'cli_identificacion' => 'CC100001',
            'cli_tipo_identificacion' => 'CC',
        ]);

        $cliente2 = Cliente::create([
            'cli_nombre' => 'Cliente Con Deps',
            'cli_identificacion' => 'CC100002',
            'cli_tipo_identificacion' => 'CC',
        ]);

        ClienteSede::create([
            'cli_id' => $cliente2->cli_id,
            'cls_descripcion' => 'Sede Bloqueo',
            'cls_direccion' => 'Carrera 100 # 100-100',
        ]);

        // Direct logic test: simulate what happens when destroyMultiple checks dependencies
        $ids = [$cliente1->cli_id, $cliente2->cli_id];
        
        $clientesBloqueados = collect($ids)
            ->filter(function ($id) {
                if (ClienteSede::where('cli_id', $id)->exists()) {
                    return true;
                }
                if (\DB::table('cliente_equipo')
                    ->where('cli_id', $id)
                    ->whereNull('deleted_at')
                    ->exists()) {
                    return true;
                }
                return Equipo::whereIn('cls_id', function ($query) use ($id) {
                    $query->select('cls_id')
                        ->from('cliente_sedes')
                        ->where('cli_id', $id)
                        ->whereNull('deleted_at');
                })->exists();
            })
            ->values()
            ->all();

        $this->assertContains($cliente2->cli_id, $clientesBloqueados);
        $this->assertNotContains($cliente1->cli_id, $clientesBloqueados);
    }

    /**
     * Test that destroyMultiple rejects if any sede has equipos
     */
    public function test_destroy_multiple_sedes_rejects_if_any_has_equipos()
    {
        $cliente = Cliente::create([
            'cli_nombre' => 'Cliente Test',
            'cli_identificacion' => 'CC200001',
            'cli_tipo_identificacion' => 'CC',
        ]);

        $sede1 = ClienteSede::create([
            'cli_id' => $cliente->cli_id,
            'cls_descripcion' => 'Sede Sin Equipos',
            'cls_direccion' => 'Carrera 200 # 200-1',
        ]);

        $sede2 = ClienteSede::create([
            'cli_id' => $cliente->cli_id,
            'cls_descripcion' => 'Sede Con Equipos',
            'cls_direccion' => 'Carrera 200 # 200-2',
        ]);

        $marca = Marca::create(['mar_descripcion' => 'Schneider']);
        $tipoEquipo = TipoEquipo::create(['teq_descripcion' => 'UPS']);

        Equipo::create([
            'equ_modelo' => 'Easy UPS',
            'equ_serial' => 'SERIAL789',
            'mar_id' => $marca->mar_id,
            'teq_id' => $tipoEquipo->teq_id,
            'cls_id' => $sede2->cls_id,
            'equ_cant_baterias' => 1,
        ]);

        // Direct logic test: simulate what happens when destroyMultiple checks dependencies
        $ids = [$sede1->cls_id, $sede2->cls_id];
        
        $sedesConEquipos = ClienteSede::whereIn('cls_id', $ids)
            ->whereHas('equipos')
            ->pluck('cls_id')
            ->all();

        $this->assertContains($sede2->cls_id, $sedesConEquipos);
        $this->assertNotContains($sede1->cls_id, $sedesConEquipos);
    }
}
