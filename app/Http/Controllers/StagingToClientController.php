<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StagingToClientController extends Controller
{
    /**
     * Procesa los registros pendientes en la tabla staging y los inserta/actualiza en la tabla de clientes.
     */
    public function migrateClients(Request $request)
    {
        set_time_limit(0); // Previene el timeout durante la migración de registros

        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return response()->json(['error' => 'El campo batch_id es obligatorio para filtrar la importación.'], 400);
        }

        // Obtener los registros de la tabla staging que están pendientes para este lote
        $stagingRecords = DB::table('excel_import_staging')
            ->where('import_batch_id', $batchId)
            ->where('import_status', 'pendiente')
            ->get();

        if ($stagingRecords->isEmpty()) {
            return response()->json(['message' => 'No se encontraron registros pendientes para procesar en este lote.'], 404);
        }

        $processed = 0;
        $errors = 0;

        foreach ($stagingRecords as $record) {
            try {
                // Validar que tengamos la identificación necesaria
                if (empty($record->eis_identificacion)) {
                    throw new \Exception("Identificación de cliente faltante.");
                }

                DB::transaction(function () use ($record, &$processed) {
                    // 1. Insertar o actualizar el Cliente en la tabla final
                    DB::table('cliente')->updateOrInsert(
                        ['cli_identificacion' => $record->eis_identificacion],
                        [
                            'cli_nombre' => $record->eis_nombre_empresa_persona ?? 'CLIENTE SIN NOMBRE',
                            'cli_tipo_identificacion' => $record->eis_tipo_identificacion ?? 'CC/NIT',
                            'updated_at' => now(),
                            'created_at' => now()
                        ]
                    );

                    $clienteId = DB::table('cliente')
                        ->where('cli_identificacion', $record->eis_identificacion)
                        ->value('cli_id');

                    if (!$clienteId) {
                        throw new \Exception('No se pudo obtener el cliente insertado/actualizado.');
                    }

                    // 2. Insertar o actualizar la sede del cliente con datos de staging
                    DB::table('cliente_sedes')->updateOrInsert(
                        [
                            'cli_id' => $clienteId,
                            'cls_descripcion' => $record->eis_sede ?? 'Sede principal',
                        ],
                        [
                            'cls_direccion' => $record->eis_direccion,
                            'cls_departamento' => $record->eis_departamento,
                            'cls_ciudad' => $record->eis_ciudad,
                            'cls_telefono' => $record->eis_telefono_contacto_2 ?? $record->eis_telefono_contacto_2,
                            'cls_correo' => $record->eis_correo_contacto_2 ?? $record->eis_correo_contacto_2,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    // 3. Marcar el registro como procesado en la tabla staging
                    DB::table('excel_import_staging')
                        ->where('id', $record->id)
                        ->update([
                            'import_status' => 'procesado',
                            'import_error' => null,
                            'updated_at' => now()
                        ]);
                    
                    $processed++;
                });

            } catch (\Exception $e) {
                $errors++;
                DB::table('excel_import_staging')->where('id', $record->id)->update([
                    'import_status' => 'error',
                    'import_error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'message' => 'Migración de clientes finalizada.',
            'summary' => ['procesados' => $processed, 'errores' => $errors]
        ], 200);
    }
}
