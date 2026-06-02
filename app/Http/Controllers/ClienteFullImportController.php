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
        // Verificar si la extensión zip está cargada
        if (!extension_loaded('zip')) {
            return response()->json(['error' => 'La extensión PHP "zip" no está habilitada en el servidor.'], 500);
        }

        // Validar el archivo
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $reader = IOFactory::createReaderForFile($file->getPathname());
        $reader->setReadDataOnly(true); // Mejora el rendimiento significativamente
        $spreadsheet = $reader->load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) <= 1) {
            return response()->json(['error' => 'El archivo está vacío o no tiene datos válidos'], 400);
        }

        $batchId = (string) Str::uuid();
        $processedCount = 0;
        $erroresImportacion = [];
        $insertData = [];
        $now = now();

        try {
            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                if ($index == 0) continue; // Omitir encabezados

                if (count($row) < 32) {
                    $erroresImportacion[] = [
                        'fila' => $index + 1,
                        'error' => 'Fila incompleta - faltan columnas'
                    ];
                    continue;
                }

                $insertData[] = [
                    'col_00_sector_empresa' => $row[1] ?? null,
                    'col_01_tipo_cliente' => $row[2] ?? null,
                    'col_02_sigla' => $row[3] ?? null,
                    'col_03_nombre_empresa_persona' => $row[4] ?? null,
                    'col_04_tipo_identificacion' => $row[5] ?? null,
                    'col_05_identificacion' => $row[6] ?? null,
                    'col_06_dv' => $row[7] ?? null,
                    'col_07_departamento' => $row[8] ?? null,
                    'col_08_ciudad' => $row[9] ?? null,
                    'col_09_direccion' => $row[10] ?? null,
                    'col_10_sede' => $row[11] ?? null,
                    'col_11_ubicacion_equipo' => $row[12] ?? null,
                    'col_12_nombre_contacto_1' => $row[13] ?? null,
                    'col_13_correo_contacto_1' => $row[14] ?? null,
                    'col_14_telefono_contacto_1' => $row[15] ?? null,
                    'col_15_nombre_contacto_2' => $row[16] ?? null,
                    'col_16_correo_contacto_2' => $row[17] ?? null,
                    'col_17_telefono_contacto_2' => $row[18] ?? null,
                    'col_18_estado_cliente' => $row[19] ?? null,
                    'col_19_tipo_relacion_comercial' => $row[20] ?? null,
                    'col_20_marca_equipo' => $row[21] ?? null,
                    'col_21_modelo_equipo' => $row[22] ?? null,
                    'col_22_potencia_kva' => $row[23] ?? null,
                    'col_23_serial_equipo' => $row[24] ?? null,
                    'col_24_cantidad_baterias_int' => $row[25] ?? null,
                    'col_25_cantidad_baterias_ext' => $row[26] ?? null,
                    'col_26_marca_bateria' => $row[27] ?? null,
                    'col_27_referencia_bateria' => $row[28] ?? null,
                    'col_28_voltaje_bateria' => $row[29] ?? null,
                    'col_29_amperaje_bateria' => $row[30] ?? null,
                    'col_30_snmps' => $row[31] ?? null,
                    'import_status' => 'pendiente',
                    'import_batch_id' => $batchId,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                if (count($insertData) >= 100) {
                    ExcelImportStaging::insert($insertData);
                    $processedCount += count($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                ExcelImportStaging::insert($insertData);
                $processedCount += count($insertData);
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
}
