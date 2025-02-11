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
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Omitir encabezados
        
            $insertData[] = [
                'cli_nombre' => $row[0] ?? null,
                'cli_identificacion' => $row[1] ?? null,
                'cli_tipo_identificacion' => $row[2] ?? null, // Asegúrate de que esta sea la columna correcta
                'created_at' => now(), // Agregar timestamp si la tabla lo usa
                'updated_at' => now()
            ];
        }

        // Insertar en la base de datos
        if (!empty($insertData)) {
            DB::table('cliente')->insert($insertData);
            return response()->json(['message' => 'Archivo procesado con éxito'], 200);
        }

        return response()->json(['error' => 'No se pudieron procesar los datos'], 500);
    }
}
