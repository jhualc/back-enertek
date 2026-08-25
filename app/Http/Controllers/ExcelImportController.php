<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExcelImportController extends Controller
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
        $erroresImportacion = [];
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Omitir encabezados

            $identificacion = trim((string) ($row[1] ?? ''));
            if ($identificacion === '') {
                $erroresImportacion[] = [
                    'fila' => $index + 1,
                    'error' => 'La identificación del cliente es obligatoria'
                ];
                continue;
            }
        
            $insertData[] = [
                'cli_nombre' => $row[0] ?? null,
                'cli_identificacion' => $identificacion,
                'cli_tipo_identificacion' => $row[2] ?? null, // Asegúrate de que esta sea la columna correcta
                'created_at' => now(), // Agregar timestamp si la tabla lo usa
                'updated_at' => now()
            ];
        }

        // Insertar en la base de datos
        if (!empty($insertData)) {
            DB::table('cliente')->insert($insertData);
            return response()->json([
                'message' => empty($erroresImportacion)
                    ? 'Archivo procesado con éxito'
                    : 'Archivo procesado con errores',
                'errorCount' => count($erroresImportacion),
                'errors' => $erroresImportacion
            ], 200);
        }

        if (!empty($erroresImportacion)) {
            return response()->json([
                'message' => 'No se insertaron registros porque la identificación es obligatoria',
                'errorCount' => count($erroresImportacion),
                'errors' => $erroresImportacion
            ], 422);
        }

        return response()->json(['error' => 'No se pudieron procesar los datos'], 500);
    }
}
