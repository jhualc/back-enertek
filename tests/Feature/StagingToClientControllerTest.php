<?php

namespace Tests\Feature;

use App\Http\Controllers\StagingToClientController;
use Tests\TestCase;

class StagingToClientControllerTest extends TestCase
{
    public function test_resolve_cliente_identificacion_uses_fallback_for_blank_values(): void
    {
        $controller = new StagingToClientController();
        $record = (object) [
            'id' => 42,
            'eis_identificacion' => '   ',
            'eis_nombre_empresa_persona' => 'Acme S.A.S.'
        ];

        $method = new \ReflectionMethod($controller, 'resolveClienteIdentificacion');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $record);

        $this->assertStringStartsWith('SIN_IDENTIFICACION_', $result);
        $this->assertStringContainsString('Acme', $result);
        $this->assertStringContainsString('42', $result);
    }
}
