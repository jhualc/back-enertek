<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\ExcelImportProcessorController;
use App\Traits\ImportResultsTrait;

class ImportResultsTraitTest extends TestCase
{
    /**
     * Test que ImportResultsTrait genera CSV correctamente
     */
    public function test_generate_import_results_csv()
    {
        // Crear un controlador para usar el trait
        $controller = new class {
            use ImportResultsTrait;
        };

        // Datos de prueba
        $results = [
            [
                'row' => 2,
                'status' => 'success',
                'error' => null,
                'data' => [
                    'eis_identificacion' => '1234567890',
                    'eis_nombre_empresa_persona' => 'Empresa XYZ',
                    'eis_tipo_identificacion' => 'NIT',
                    'eis_sede' => 'Bogotá',
                    'eis_marca_equipo' => 'APC',
                    'eis_modelo_equipo' => 'Smart-UPS 2000',
                    'eis_serial_equipo' => 'A1B2C3D4',
                ]
            ],
            [
                'row' => 3,
                'status' => 'error',
                'error' => 'Identificación de cliente requerida',
                'data' => [
                    'eis_identificacion' => '',
                    'eis_nombre_empresa_persona' => 'Empresa Sin ID',
                    'eis_tipo_identificacion' => '',
                    'eis_sede' => '',
                    'eis_marca_equipo' => '',
                    'eis_modelo_equipo' => '',
                    'eis_serial_equipo' => '',
                ]
            ],
        ];

        // Generar CSV
        $response = $controller->generateImportResultsCsv($results, 'test_results.csv');

        // Validaciones
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('test_results.csv', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test que generateImportSummary calcula estadísticas correctamente
     */
    public function test_generate_import_summary()
    {
        $controller = new class {
            use ImportResultsTrait;
        };

        $results = [
            ['row' => 2, 'status' => 'success', 'error' => null, 'data' => []],
            ['row' => 3, 'status' => 'success', 'error' => null, 'data' => []],
            ['row' => 4, 'status' => 'error', 'error' => 'Error message', 'data' => []],
            ['row' => 5, 'status' => 'error', 'error' => 'Another error', 'data' => []],
        ];

        $summary = $controller->generateImportSummary($results);

        $this->assertEquals(4, $summary['total']);
        $this->assertEquals(2, $summary['exitosos']);
        $this->assertEquals(2, $summary['errores']);
        $this->assertEquals(50.0, $summary['porcentaje_exito']);
    }

    /**
     * Test que generateImportSummary maneja lista vacía
     */
    public function test_generate_import_summary_empty()
    {
        $controller = new class {
            use ImportResultsTrait;
        };

        $summary = $controller->generateImportSummary([]);

        $this->assertEquals(0, $summary['total']);
        $this->assertEquals(0, $summary['exitosos']);
        $this->assertEquals(0, $summary['errores']);
        $this->assertEquals(0, $summary['porcentaje_exito']);
    }

    /**
     * Test que generateImportSummary calcula 100% éxito
     */
    public function test_generate_import_summary_all_success()
    {
        $controller = new class {
            use ImportResultsTrait;
        };

        $results = [
            ['row' => 2, 'status' => 'success', 'error' => null, 'data' => []],
            ['row' => 3, 'status' => 'success', 'error' => null, 'data' => []],
            ['row' => 4, 'status' => 'success', 'error' => null, 'data' => []],
        ];

        $summary = $controller->generateImportSummary($results);

        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(3, $summary['exitosos']);
        $this->assertEquals(0, $summary['errores']);
        $this->assertEquals(100.0, $summary['porcentaje_exito']);
    }
}
