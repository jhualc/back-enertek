<?php

namespace Tests\Feature;

use App\Http\Controllers\ClientFullImportController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

    public function test_find_column_index_distinguishes_identificacion_from_tipo_identificacion_headers(): void
    {
        $controller = new ClientFullImportController();

        $method = new \ReflectionMethod($controller, 'findColumnIndex');
        $method->setAccessible(true);

        $headers = ['Identificación', 'Tipo Identificación'];

        $identificacionIndex = $method->invoke($controller, $headers, ['identificacion']);
        $tipoIdentificacionIndex = $method->invoke($controller, $headers, ['tipo identificacion', 'tipo de identificacion', 'tipo_identificacion']);

        $this->assertSame(0, $identificacionIndex);
        $this->assertSame(1, $tipoIdentificacionIndex);
    }

    public function test_upload_excel_persists_identificacion_and_tipo_identificacion_values_in_staging(): void
    {
        DB::table('excel_import_staging')->truncate();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Nombre Empresa', 'Tipo Identificación', 'Identificación'],
            ['Empresa Demo', 'CC', '123456789'],
        ], null, 'A1');

        $tempFile = tempnam(sys_get_temp_dir(), 'import-test-');
        $xlsxPath = $tempFile . '.xlsx';
        rename($tempFile, $xlsxPath);

        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxPath);

        $request = new Request();
        $request->files->set('file', new UploadedFile($xlsxPath, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true));

        $controller = new ClientFullImportController();
        $response = $controller->uploadExcel($request);

        $this->assertSame(200, $response->getStatusCode());

        $record = DB::table('excel_import_staging')->latest('id')->first();

        $this->assertNotNull($record);
        $this->assertSame('CC', $record->eis_tipo_identificacion);
        $this->assertSame('123456789', $record->eis_identificacion);

        unlink($xlsxPath);
    }
}
