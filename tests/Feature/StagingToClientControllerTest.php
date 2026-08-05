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

    public function test_resolve_telefono_sede_prefers_movil_2_then_movil_1(): void
    {
        $controller = new StagingToClientController();

        $method = new \ReflectionMethod($controller, 'resolveTelefonoSede');
        $method->setAccessible(true);

        $recordWithMovil2 = (object) [
            'eis_telefono_contacto_2' => '3001112222',
            'eis_telefono_contacto_1' => '3003334444',
        ];
        $recordWithMovil1 = (object) [
            'eis_telefono_contacto_2' => '',
            'eis_telefono_contacto_1' => '3003334444',
        ];

        $this->assertSame('3001112222', $method->invoke($controller, $recordWithMovil2));
        $this->assertSame('3003334444', $method->invoke($controller, $recordWithMovil1));
    }
}
