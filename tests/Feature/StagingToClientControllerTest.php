<?php

namespace Tests\Feature;

use App\Http\Controllers\StagingToClientController;
use Tests\TestCase;

class StagingToClientControllerTest extends TestCase
{
    public function test_resolve_cliente_identificacion_rejects_blank_values(): void
    {
        $controller = new StagingToClientController();
        $record = (object) [
            'eis_identificacion' => '   ',
        ];

        $method = new \ReflectionMethod($controller, 'resolveClienteIdentificacion');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Identificación de cliente requerida');
        $method->invoke($controller, $record);
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
