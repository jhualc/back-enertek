<?php

namespace Tests\Feature;

use App\Http\Controllers\ClientFullImportController;
use Tests\TestCase;

class ClientFullImportControllerTest extends TestCase
{
    public function test_find_column_index_matches_headers_with_pipes_and_accents(): void
    {
        $controller = new ClientFullImportController();

        $method = new \ReflectionMethod($controller, 'findColumnIndex');
        $method->setAccessible(true);

        $headers = ['Nombre empresa Persona', 'Tipo Identificación', 'Identificación'];

        $nombreEmpresaIndex = $method->invoke($controller, $headers, ['nombre empresa', 'Nombre empresa Persona']);
        $tipoIdentificacionIndex = $method->invoke($controller, $headers, ['tipo identificacion', 'tipo de identificacion', 'tipo_identificacion']);
        $identificacionIndex = $method->invoke($controller, $headers, ['identificacion']);

        $this->assertSame(0, $nombreEmpresaIndex);
        $this->assertSame(1, $tipoIdentificacionIndex);
        $this->assertSame(2, $identificacionIndex);
    }
}
