<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClientFullImportController extends Controller
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

        // Verifica que el archivo tiene datos (encabezado + al menos una fila)
        if (count($rows) <= 1) {
            return response()->json(['error' => 'El archivo está vacío o no tiene datos válidos'], 400);
        }

        // Procesar los datos
        $insertData = [];
        $batchId = time(); // Identificador temporal para el lote de importación

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Omitir encabezados
        
            // Mapeo de columnas según la migración excel_import_staging
            $insertData[] = [
                'eis_sector_empresa'          => $row[0] ?? null,
                'eis_tipo_cliente'            => $row[1] ?? null,
                'eis_sigla'                   => $row[2] ?? null,
                'eis_nombre_empresa_persona'  => $row[3] ?? null,
                'eis_tipo_identificacion'     => $row[4] ?? null,
                'eis_identificacion'          => $row[5] ?? null,
                'eis_dv'                      => $row[6] ?? null,
                'eis_departamento'            => $row[7] ?? null,
                'eis_ciudad'                  => $row[8] ?? null,
                'eis_direccion'               => $row[9] ?? null,
                'eis_sede'                    => $row[10] ?? null,
                'eis_ubicacion_equipo'        => $row[11] ?? null,
                'eis_nombre_contacto_1'       => $row[12] ?? null,
                'eis_correo_contacto_1'       => $row[13] ?? null,
                'eis_telefono_contacto_1'     => $row[14] ?? null,
                'eis_nombre_contacto_2'       => $row[15] ?? null,
                'eis_correo_contacto_2'       => $row[16] ?? null,
                'eis_telefono_contacto_2'     => $row[17] ?? null,
                'eis_estado_cliente'          => $row[18] ?? null,
                'eis_tipo_relacion_comercial' => $row[19] ?? null,
                'eis_marca_equipo'            => $row[20] ?? null,
                'eis_modelo_equipo'           => $row[21] ?? null,
                'eis_potencia_kva'            => $row[22] ?? null,
                'eis_serial_equipo'           => $row[23] ?? null,
                'eis_cantidad_baterias_int'   => isset($row[24]) && $row[24] !== '' ? (int)$row[24] : null,
                'eis_cantidad_baterias_ext'   => isset($row[25]) && $row[25] !== '' ? (int)$row[25] : null,
                'eis_marca_bateria'           => $row[26] ?? null,
                'eis_referencia_bateria'      => $row[27] ?? null,
                'eis_voltaje_bateria'         => $row[28] ?? null,
                'eis_amperaje_bateria'        => $row[29] ?? null,
                'eis_snmps'                   => $row[30] ?? null,
                'import_status'               => 'pendiente',
                'import_batch_id'             => $batchId,
                'created_at'                  => now(),
                'updated_at'                  => now()
            ];
        }

        // Insertar en la tabla staging (preparación)
        if (!empty($insertData)) {
            DB::table('excel_import_staging')->insert($insertData);
            
            return response()->json([
                'message' => 'Datos cargados exitosamente en el área de preparación',
                'batch_id' => $batchId,
                'count' => count($insertData)
            ], 200);
        }

        return response()->json(['error' => 'No se pudieron procesar los datos del archivo'], 500);
    }
}
