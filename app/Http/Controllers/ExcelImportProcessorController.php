<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ExcelImportStaging;
use App\Models\Cliente;
use App\Models\ClienteSede;
use App\Models\Persona;
use App\Models\Equipo;
use App\Models\Bateria;
use App\Models\BateriaEquipo;
use App\Models\Marca;
use App\Models\TipoEquipo;

class ExcelImportProcessorController extends Controller
{
    public function processBatch(Request $request)
    {
        $batchId = $request->input('batch_id');
        
        if (!$batchId) {
            return response()->json(['error' => 'batch_id requerido'], 400);
        }

        $stagingRecords = ExcelImportStaging::where('import_batch_id', $batchId)
            ->where('import_status', 'pendiente')
            ->get();

        if ($stagingRecords->isEmpty()) {
            return response()->json(['error' => 'No hay registros pendientes para este batch'], 404);
        }

        $successCount = 0;
        $errorCount = 0;

        try {
            DB::beginTransaction();

            foreach ($stagingRecords as $record) {
                try {
                    $this->processStagingRecord($record);
                    $record->update([
                        'import_status' => 'procesado',
                        'import_error' => null
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $record->update([
                        'import_status' => 'error',
                        'import_error' => $e->getMessage()
                    ]);
                    $errorCount++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Batch {$batchId} procesado",
                'successCount' => $successCount,
                'errorCount' => $errorCount,
                'totalRecords' => $stagingRecords->count()
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al procesar batch: ' . $e->getMessage()], 500);
        }
    }

    private function processStagingRecord($record)
    {
        $identificacion = $record->col_05_identificacion;

        if (!$identificacion) {
            throw new \Exception("Identificación de cliente requerida");
        }

        // 1. Crear o actualizar Cliente
        $cliente = Cliente::firstOrCreate(
            ['cli_identificacion' => $identificacion],
            [
                'cli_nombre' => $record->col_03_nombre_empresa_persona,
                'cli_tipo_identificacion' => $record->col_04_tipo_identificacion,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 2. Crear ClienteSede si existe sede y dirección
        if ($record->col_10_sede && $record->col_09_direccion) {
            ClienteSede::firstOrCreate(
                [
                    'cli_id' => $cliente->cli_id,
                    'cls_descripcion' => $record->col_10_sede
                ],
                [
                    'cls_direccion' => $record->col_09_direccion,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // 3. Crear Personas (contactos)
        if ($record->col_12_nombre_contacto_1 && $record->col_13_correo_contacto_1) {
            Persona::firstOrCreate(
                ['per_correo' => $record->col_13_correo_contacto_1],
                [
                    'per_nombre' => $record->col_12_nombre_contacto_1,
                    'per_cargo' => 'Contacto',
                    'per_empresa' => $record->col_03_nombre_empresa_persona,
                    'per_tipo_persona' => 'Cliente',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        if ($record->col_15_nombre_contacto_2 && $record->col_16_correo_contacto_2) {
            Persona::firstOrCreate(
                ['per_correo' => $record->col_16_correo_contacto_2],
                [
                    'per_nombre' => $record->col_15_nombre_contacto_2,
                    'per_cargo' => 'Contacto',
                    'per_empresa' => $record->col_03_nombre_empresa_persona,
                    'per_tipo_persona' => 'Cliente',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // 4. Crear Marca equipo si no existe
        $marIdEquipo = null;
        if ($record->col_20_marca_equipo) {
            $marcaEquipo = Marca::firstOrCreate(
                ['mar_descripcion' => $record->col_20_marca_equipo],
                ['created_at' => now(), 'updated_at' => now()]
            );
            $marIdEquipo = $marcaEquipo->mar_id;
        }

        // 5. Obtener TipoEquipo (crear genérico si no existe)
        $tipoEquipo = TipoEquipo::firstOrCreate(
            ['teq_descripcion' => 'UPS'],
            ['created_at' => now(), 'updated_at' => now()]
        );
        $teqId = $tipoEquipo->teq_id;

        // 6. Crear Equipo si existe modelo y serial
        if ($record->col_21_modelo_equipo && $record->col_23_serial_equipo) {
            $equipo = Equipo::create([
                'equ_modelo' => $record->col_21_modelo_equipo,
                'equ_serial' => $record->col_23_serial_equipo,
                'equ_potencia' => $record->col_22_potencia_kva,
                'equ_ubicacion' => $record->col_11_ubicacion_equipo,
                'equ_cant_baterias' => ($record->col_24_cantidad_baterias_int ?? 0) + ($record->col_25_cantidad_baterias_ext ?? 0),
                'mar_id' => $marIdEquipo,
                'teq_id' => $teqId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 7. Crear relación Cliente-Equipo
            DB::table('cliente_equipo')->insertOrIgnore([
                'cli_id' => $cliente->cli_id,
                'equ_id' => $equipo->equ_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 8. Crear Batería si existen marca y voltaje
            if ($record->col_26_marca_bateria && $record->col_28_voltaje_bateria) {
                $marcaBateria = Marca::firstOrCreate(
                    ['mar_descripcion' => $record->col_26_marca_bateria],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                $bateria = Bateria::create([
                    'bat_modelo' => $record->col_27_referencia_bateria ?? 'N/A',
                    'bat_voltaje' => $record->col_28_voltaje_bateria,
                    'bat_capacidad' => $record->col_29_amperaje_bateria,
                    'mar_id' => $marcaBateria->mar_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // 9. Crear relación Batería-Equipo
                BateriaEquipo::create([
                    'equ_id' => $equipo->equ_id,
                    'bat_id' => $bateria->bat_id,
                    'beq_fecha' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    public function getErrors(Request $request)
    {
        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return response()->json(['error' => 'batch_id requerido'], 400);
        }

        $errors = ExcelImportStaging::where('import_batch_id', $batchId)
            ->where('import_status', 'error')
            ->select('id', 'col_03_nombre_empresa_persona', 'col_05_identificacion', 'import_error')
            ->get();

        return response()->json([
            'batchId' => $batchId,
            'errorCount' => $errors->count(),
            'errors' => $errors
        ], 200);
    }

    public function getBatchStatus(Request $request)
    {
        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return response()->json(['error' => 'batch_id requerido'], 400);
        }

        $stats = ExcelImportStaging::where('import_batch_id', $batchId)
            ->selectRaw('import_status, COUNT(*) as count')
            ->groupBy('import_status')
            ->pluck('count', 'import_status');

        return response()->json([
            'batchId' => $batchId,
            'pendiente' => $stats['pendiente'] ?? 0,
            'procesado' => $stats['procesado'] ?? 0,
            'error' => $stats['error'] ?? 0,
            'total' => $stats->sum()
        ], 200);
    }
}
