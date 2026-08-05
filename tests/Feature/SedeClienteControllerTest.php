<?php

namespace Tests\Feature;

use App\Http\Controllers\SedeClienteController;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class SedeClienteControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_by_cliente_filters_by_route_client_id_when_provided(): void
    {
        $mock = Mockery::mock('alias:App\\Models\\ClienteSede');
        $mock->shouldReceive('with')->with('cliente')->andReturnSelf();
        $mock->shouldReceive('whereNull')->with('deleted_at')->andReturnSelf();
        $mock->shouldReceive('where')->with('cli_id', 36)->andReturnSelf();
        $mock->shouldReceive('get')->andReturn(collect([]));

        $controller = new SedeClienteController();
        $response = $controller->byCliente('36');

        $this->assertSame(200, $response->getStatusCode());
    }
}
