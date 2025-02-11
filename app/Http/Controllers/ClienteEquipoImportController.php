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

        // Verifica que el archivo tiene datos
        if (count($rows) <= 1) {
            return response()->json(['error' => 'El archivo está vacío o no tiene datos válidos'], 400);
        }

        // Procesar los datos
        $insertData = [];
        $clienteEquipoData = [];
        $erroresImportacion = [];

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Omitir encabezados

            // Buscar IDs en la base de datos
            $marca = DB::table('marca')->where('mar_descripcion', $row[2])->first();
            $mar_id = $marca ? $marca->mar_id : null;

            $tipoEquipo = DB::table('tipo_equipo')->where('teq_descripcion', $row[3])->first();
            $teq_id = $tipoEquipo ? $tipoEquipo->teq_id : null;

            $cliente = DB::table('cliente')->where('cli_identificacion', $row[5])->first();
            $cli_id = $cliente ? $cliente->cli_id : null;

            // Si hay errores, guardarlos en el archivo de errores
            if (!$mar_id || !$teq_id || !$cli_id) {
                $erroresImportacion[] = [
                    'equ_modelo' => $row[0] ?? null,
                    'equ_serial' => $row[1] ?? null,
                    'mar_descripcion' => $row[2] ?? null,
                    'teq_descripcion' => $row[3] ?? null,
                    'equ_cant_baterias' => $row[4] ?? null,
                    'cli_identificacion' => $row[5] ?? null,
                    'error' => json_encode([
                        'mar_id' => $mar_id ? 'OK' : "No encontrado ({$row[2]})",
                        'teq_id' => $teq_id ? 'OK' : "No encontrado ({$row[3]})",
                        'cli_id' => $cli_id ? 'OK' : "No encontrado ({$row[5]})"
                    ])
                ];
                continue; // Omitir inserción si hay errores
            }

            // Insertar equipo
            $equipoId = DB::table('equipo')->insertGetId([
                'equ_modelo' => $row[0] ?? null,
                'equ_serial' => $row[1] ?? null,
                'mar_id' => $mar_id, 
                'teq_id' => $teq_id, 
                'equ_cant_baterias' => $row[4] ?? null, 
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insertar en la tabla cliente_equipo
            $clienteEquipoData[] = [
                'cli_id' => $cli_id,
                'equ_id' => $equipoId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insertar las relaciones en la tabla cliente_equipo
        if (!empty($clienteEquipoData)) {
            DB::table('cliente_equipo')->insert($clienteEquipoData);
        }

        // Si hay errores, generar el archivo Excel
        if (!empty($erroresImportacion)) {
            $filePath = $this->generateErrorExcel($erroresImportacion);
            return response()->json(['message' => 'Archivo procesado con errores', 'error_file' => $filePath], 200);
        }

        return response()->json(['message' => 'Archivo procesado con éxito'], 200);
    }

    /**
     * Genera un archivo Excel con los errores encontrados durante la importación.
     */
    private function generateErrorExcel($errors)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados del archivo
        $headers = ['Modelo', 'Serial', 'Marca', 'Tipo Equipo', 'Cantidad Baterías', 'Identificación Cliente', 'Errores'];
        $sheet->fromArray([$headers], null, 'A1');

        // Agregar los datos de error al archivo
        $rowIndex = 2;
        foreach ($errors as $error) {
            $sheet->fromArray([$error], null, "A$rowIndex");
            $rowIndex++;
        }

        // Guardar el archivo en storage/app/errors_import.xlsx
        $fileName = 'errors_import_' . time() . '.xlsx';
        $filePath = storage_path('app/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return asset('storage/' . $fileName);
    }
}
