<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClienteEquipoImportController extends Controller
{
    public function uploadExcel(Request $request)
    {
        // Validar el archivo
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) <= 1) {
            return response()->json(['error' => 'El archivo está vacío o no tiene datos válidos'], 400);
        }

        $insertData = [];
        $clienteEquipoData = [];
        $erroresImportacion = [];

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Omitir encabezados

            // Verificar que la fila tiene al menos 7 columnas
            if (count($row) < 7) {
                $erroresImportacion[] = array_merge($row, ['error' => 'Fila incompleta']);
                continue;
            }

            // Extraer valores con manejo de índices indefinidos
            $equ_modelo = $row[0] ?? null;
            $equ_serial = $row[1] ?? null;
            $mar_descripcion = $row[2] ?? null;
            $teq_descripcion = $row[3] ?? null;
            $equ_cant_baterias = $row[4] ?? null;
            $cli_identificacion = $row[5] ?? null;
            $equ_ubicacion = $row[6] ?? null;

            // Obtener IDs desde la base de datos
            $marca = $mar_descripcion ? DB::table('marca')->where('mar_descripcion', $mar_descripcion)->first() : null;
            $mar_id = $marca->mar_id ?? null;

            $tipoEquipo = $teq_descripcion ? DB::table('tipo_equipo')->where('teq_descripcion', $teq_descripcion)->first() : null;
            $teq_id = $tipoEquipo->teq_id ?? null;

            $cliente = $cli_identificacion ? DB::table('cliente')->where('cli_identificacion', $cli_identificacion)->first() : null;
            $cli_id = $cliente->cli_id ?? null;

            if (!$mar_id || !$teq_id || !$cli_id) {
                $erroresImportacion[] = [
                    'equ_modelo' => $equ_modelo,
                    'equ_serial' => $equ_serial,
                    'mar_descripcion' => $mar_descripcion,
                    'teq_descripcion' => $teq_descripcion,
                    'equ_cant_baterias' => $equ_cant_baterias,
                    'cli_identificacion' => $cli_identificacion,
                    'equ_ubicacion' => $equ_ubicacion,
                    'error' => json_encode([
                        'mar_id' => $mar_id ? 'OK' : "No encontrado ({$mar_descripcion})",
                        'teq_id' => $teq_id ? 'OK' : "No encontrado ({$teq_descripcion})",
                        'cli_id' => $cli_id ? 'OK' : "No encontrado ({$cli_identificacion})"
                    ])
                ];
                continue;
            }

            $equipoId = DB::table('equipo')->insertGetId([
                'equ_modelo' => $equ_modelo,
                'equ_serial' => $equ_serial,
                'mar_id' => $mar_id,
                'teq_id' => $teq_id,
                'equ_cant_baterias' => $equ_cant_baterias,
                'equ_ubicacion' => $equ_ubicacion,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $clienteEquipoData[] = [
                'cli_id' => $cli_id,
                'equ_id' => $equipoId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($clienteEquipoData)) {
            DB::table('cliente_equipo')->insert($clienteEquipoData);
        }

        if (!empty($erroresImportacion)) {
            $filePath = $this->generateErrorExcel($erroresImportacion);
            return response()->json(['message' => 'Archivo procesado con errores', 'error_file' => $filePath], 200);
        }

        return response()->json(['message' => 'Archivo procesado con éxito'], 200);
    }

    private function generateErrorExcel($errors)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Modelo', 'Serial', 'Marca', 'Tipo Equipo', 'Cantidad Baterías', 'Identificación Cliente', 'Ubicación', 'Errores'];
        $sheet->fromArray([$headers], null, 'A1');

        $rowIndex = 2;
        foreach ($errors as $error) {
            $sheet->fromArray(array_values($error), null, "A$rowIndex");
            $rowIndex++;
        }

        $fileName = 'errors_import_' . time() . '.xlsx';
        $filePath = storage_path('app/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return asset('storage/' . $fileName);
    }
}
