<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\ClientFullImportController;

$path = sys_get_temp_dir() . '/test_import.xlsx';
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray([
    ['Sector', 'Tipo Cliente', 'Sigla', 'Nombre', 'Tipo Identificacion', 'Identificacion', 'DV', 'Departamento', 'Ciudad', 'Direccion', 'Sede', 'Ubicacion', 'Contacto 1', 'Correo 1', 'Telefono 1', 'Contacto 2', 'Correo 2', 'Telefono 2', 'Estado', 'Tipo Relacion', 'Marca Equipo', 'Tipo Equipo', 'Modelo Equipo', 'Potencia', 'Serial', 'Cant Baterias Int', 'Cant Baterias Ext', 'Marca Bateria', 'Referencia Bateria', 'Voltaje Bateria', 'Amperaje Bateria', 'SNMPS'],
    ['A', 'B', 'C', 'D', 'E', '12345', '1', 'Bogota', 'Bogota', 'Dir', 'Sede', 'Ub', 'Juan', 'juan@test.com', '123', 'Ana', 'ana@test.com', '456', 'Activo', 'Nuevo', 'MGE', 'UPS', 'Model', '10', 'SER123', '2', '1', 'CSB', 'BAT1', '12V', '100Ah', 'SNMP']
], null, 'A1');
$writer = new Xlsx($spreadsheet);
$writer->save($path);

$request = new Request();
$request->files->set('file', new UploadedFile($path, 'test_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true));

$controller = new ClientFullImportController();
$response = $controller->uploadExcel($request);
$body = $response->getContent();
echo $body . PHP_EOL;

unlink($path);
