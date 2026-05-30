<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ExcelImportStaging;
use Illuminate\Support\Str;

class ClienteFullImportController extends Controller
{
    public function uploadExcel(Request $request)
    {
        // Validar el archivo
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
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

        $batchId = Str::uuid();
        $processedCount = 0;
        $erroresImportacion = [];

        try {
            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                if ($index == 0) continue; // Omitir encabezados

                if (count($row) < 31) {
                    $erroresImportacion[] = [
                        'fila' => $index + 1,
                        'error' => 'Fila incompleta - faltan columnas'
                    ];
                    continue;
                }

                try {
                    $this->insertToStaging($row, $batchId);
                    $processedCount++;
                } catch (\Exception $e) {
                    $erroresImportacion[] = [
                        'fila' => $index + 1,
                        'nombreEmpresa' => $row[3] ?? 'N/A',
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            if (!empty($erroresImportacion)) {
                return response()->json([
                    'message' => "Excel cargado a staging con {$processedCount} registros",
                    'batchId' => $batchId,
                    'processedCount' => $processedCount,
                    'errorCount' => count($erroresImportacion),
                    'errors' => $erroresImportacion
                ], 200);
            }

            return response()->json([
                'message' => "Excel cargado a staging exitosamente con {$processedCount} registros",
                'batchId' => $batchId,
                'processedCount' => $processedCount
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    private function insertToStaging($row, $batchId)
    {
        ExcelImportStaging::create([
            'col_00_sector_empresa' => $row[0] ?? null,
            'col_01_tipo_cliente' => $row[1] ?? null,
            'col_02_sigla' => $row[2] ?? null,
            'col_03_nombre_empresa_persona' => $row[3] ?? null,
            'col_04_tipo_identificacion' => $row[4] ?? null,
            'col_05_identificacion' => $row[5] ?? null,
            'col_06_dv' => $row[6] ?? null,
            'col_07_departamento' => $row[7] ?? null,
            'col_08_ciudad' => $row[8] ?? null,
            'col_09_direccion' => $row[9] ?? null,
            'col_10_sede' => $row[10] ?? null,
            'col_11_ubicacion_equipo' => $row[11] ?? null,
            'col_12_nombre_contacto_1' => $row[12] ?? null,
            'col_13_correo_contacto_1' => $row[13] ?? null,
            'col_14_telefono_contacto_1' => $row[14] ?? null,
            'col_15_nombre_contacto_2' => $row[15] ?? null,
            'col_16_correo_contacto_2' => $row[16] ?? null,
            'col_17_telefono_contacto_2' => $row[17] ?? null,
            'col_18_estado_cliente' => $row[18] ?? null,
            'col_19_tipo_relacion_comercial' => $row[19] ?? null,
            'col_20_marca_equipo' => $row[20] ?? null,
            'col_21_modelo_equipo' => $row[21] ?? null,
            'col_22_potencia_kva' => $row[22] ?? null,
            'col_23_serial_equipo' => $row[23] ?? null,
            'col_24_cantidad_baterias_int' => $row[24] ?? null,
            'col_25_cantidad_baterias_ext' => $row[25] ?? null,
            'col_26_marca_bateria' => $row[26] ?? null,
            'col_27_referencia_bateria' => $row[27] ?? null,
            'col_28_voltaje_bateria' => $row[28] ?? null,
            'col_29_amperaje_bateria' => $row[29] ?? null,
            'col_30_snmps' => $row[30] ?? null,
            'import_status' => 'pendiente',
            'import_batch_id' => $batchId,
        ]);
    }
}

