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
use App\Traits\ImportResultsTrait;

class ExcelImportProcessorController extends Controller
{
    use ImportResultsTrait;

    /**
     * Procesa un batch de registros provenientes del Excel.
     *
     * Estados posibles:
     * - procesado = registro creado correctamente
     * - duplicado = cliente ya existía
     * - error = ocurrió un error durante el procesamiento
     */
    public function processBatch(Request $request)
    {
        $batchId = $request->input('batch_id');
        $returnFormat = $request->input('format', 'csv');

        if (!$batchId) {
            return response()->json([
                'error' => 'batch_id requerido'
            ], 400);
        }

        $stagingRecords = ExcelImportStaging::where('import_batch_id', $batchId)
            ->where('import_status', 'pendiente')
            ->get();

        if ($stagingRecords->isEmpty()) {
            return response()->json([
                'error' => 'No hay registros pendientes para este batch'
            ], 404);
        }

        $results = [];
        $rowNumber = 2;

        try {
            DB::beginTransaction();

            foreach ($stagingRecords as $record) {

                try {

                    /*
                     * processStagingRecord devuelve:
                     *
                     * success  -> se creó correctamente
                     * duplicate -> el cliente ya existía
                     */
                    $processResult = $this->processStagingRecord($record);

                    if ($processResult['status'] === 'duplicate') {

                        $record->update([
                            'import_status' => 'duplicado',
                            'import_error' => $processResult['message']
                        ]);

                        $results[] = [
                            'row' => $rowNumber,
                            'status' => 'duplicate',
                            'error' => $processResult['message'],
                            'data' => $record->toArray()
                        ];

                    } else {

                        $record->update([
                            'import_status' => 'procesado',
                            'import_error' => null
                        ]);

                        $results[] = [
                            'row' => $rowNumber,
                            'status' => 'success',
                            'error' => null,
                            'data' => $record->toArray()
                        ];
                    }

                } catch (\Exception $e) {

                    $record->update([
                        'import_status' => 'error',
                        'import_error' => $e->getMessage()
                    ]);

                    $results[] = [
                        'row' => $rowNumber,
                        'status' => 'error',
                        'error' => $e->getMessage(),
                        'data' => $record->toArray()
                    ];
                }

                $rowNumber++;
            }

            DB::commit();

            $summary = $this->generateImportSummary($results);

            if ($returnFormat === 'json') {
                return response()->json([
                    'message' => "Batch {$batchId} procesado",
                    'summary' => $summary,
                    'results' => $results
                ], 200);
            }

            $filename = "importacion_batch_{$batchId}_"
                . date('Y-m-d_H-i-s') . '.csv';

            return $this->generateImportResultsCsv(
                $results,
                $filename
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'Error al procesar batch: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Procesa un registro individual del staging.
     *
     * Devuelve:
     *
     * [
     *     'status' => 'success',
     *     'message' => '...'
     * ]
     *
     * o
     *
     * [
     *     'status' => 'duplicate',
     *     'message' => '...'
     * ]
     *
     * Los errores reales se lanzan mediante Exception.
     */
    private function processStagingRecord($record)
    {
        $identificacion = trim(
            (string) $record->eis_identificacion
        );

        \Log::info('IMPORT MASIVO - VALIDANDO CLIENTE', [
    'staging_id' => $record->id,
    'batch_id' => $record->import_batch_id,
    'identificacion' => $identificacion
]);

        /*
         * 1. Validar identificación
         */
        if ($identificacion === '') {
            throw new \Exception(
                'Identificación de cliente requerida'
            );
        }


        /*
         * 2. Validar si el cliente ya existe
         *
         * IMPORTANTE:
         * No usamos firstOrCreate aquí.
         *
         * Si existe, devolvemos "duplicate" y NO continuamos
         * creando sede, equipo, batería, etc.
         */
        $clienteExistente = Cliente::where(
            'cli_identificacion',
            $identificacion
        )->first();

        \Log::info('IMPORT MASIVO - RESULTADO CLIENTE', [
    'staging_id' => $record->id,
    'batch_id' => $record->import_batch_id,
    'identificacion' => $identificacion,
    'cliente_encontrado' => $clienteExistente ? true : false,
    'cli_id' => $clienteExistente?->cli_id
]);

        if ($clienteExistente) {

            return [
                'status' => 'duplicate',
                'message' => "El cliente con identificación {$identificacion} ya existe."
            ];
        }


        /*
         * 3. Crear Cliente
         */
        $cliente = Cliente::create([
            'cli_nombre' => $record->eis_nombre_empresa_persona,
            'cli_identificacion' => $identificacion,
            'cli_tipo_identificacion' => $record->eis_tipo_identificacion,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $clienteSede = null;


        /*
         * 4. Crear ClienteSede
         */
        if (
            $record->eis_sede &&
            $record->eis_direccion
        ) {

            $clienteSede = ClienteSede::firstOrCreate(
                [
                    'cli_id' => $cliente->cli_id,
                    'cls_descripcion' => $record->eis_sede
                ],
                [
                    'cls_direccion' => $record->eis_direccion,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }


        /*
         * 5. Crear Persona / Contacto 1
         */
        if (
            $record->eis_nombre_contacto_1 &&
            $record->eis_correo_contacto_1
        ) {

            Persona::firstOrCreate(
                [
                    'per_correo' =>
                        $record->eis_correo_contacto_1
                ],
                [
                    'per_nombre' =>
                        $record->eis_nombre_contacto_1,

                    'per_cargo' => 'Contacto',

                    'per_empresa' =>
                        $record->eis_nombre_empresa_persona,

                    'per_tipo_persona' => 'Cliente',

                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }


        /*
         * 6. Crear Persona / Contacto 2
         */
        if (
            $record->eis_nombre_contacto_2 &&
            $record->eis_correo_contacto_2
        ) {

            Persona::firstOrCreate(
                [
                    'per_correo' =>
                        $record->eis_correo_contacto_2
                ],
                [
                    'per_nombre' =>
                        $record->eis_nombre_contacto_2,

                    'per_cargo' => 'Contacto',

                    'per_empresa' =>
                        $record->eis_nombre_empresa_persona,

                    'per_tipo_persona' => 'Cliente',

                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }


        /*
         * 7. Crear Marca del equipo
         */
        $marIdEquipo = null;

        if ($record->eis_marca_equipo) {

            $marcaEquipo = Marca::firstOrCreate(
                [
                    'mar_descripcion' =>
                        $record->eis_marca_equipo
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            $marIdEquipo = $marcaEquipo->mar_id;
        }


        /*
         * 8. Obtener / crear TipoEquipo
         */
        $tipoEquipo = TipoEquipo::firstOrCreate(
            [
                'teq_descripcion' => 'UPS'
            ],
            [
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        $teqId = $tipoEquipo->teq_id;


        /*
         * 9. Crear Equipo
         */
        if (
            $record->eis_modelo_equipo &&
            $record->eis_serial_equipo
        ) {

            $equipo = Equipo::create([
                'equ_modelo' =>
                    $record->eis_modelo_equipo,

                'equ_serial' =>
                    $record->eis_serial_equipo,

                'equ_potencia' =>
                    $record->eis_potencia_kva,

                'equ_ubicacion' =>
                    $record->eis_ubicacion_equipo,

                'equ_cant_baterias' =>
                    ($record->eis_cantidad_baterias_int ?? 0)
                    +
                    ($record->eis_cantidad_baterias_ext ?? 0),

                'mar_id' => $marIdEquipo,

                'teq_id' => $teqId,

                'cls_id' =>
                    $clienteSede?->cls_id,

                'created_at' => now(),

                'updated_at' => now()
            ]);


            /*
             * 10. Crear relación Cliente-Equipo
             */
            DB::table('cliente_equipo')->insertOrIgnore([
                'cli_id' => $cliente->cli_id,
                'equ_id' => $equipo->equ_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);


            /*
             * 11. Crear Batería
             */
            if (
                $record->eis_marca_bateria &&
                $record->eis_referencia_bateria &&
                $record->eis_voltaje_bateria &&
                $record->eis_amperaje_bateria
            ) {

                $batMarca = trim(
                    (string) $record->eis_marca_bateria
                );

                $batModelo = trim(
                    (string) $record->eis_referencia_bateria
                );

                $batVoltaje = trim(
                    (string) $record->eis_voltaje_bateria
                );

                $batCapacidad = trim(
                    (string) $record->eis_amperaje_bateria
                );


                /*
                 * 12. Crear / obtener Marca de batería
                 */
                $marcaBateria = Marca::firstOrCreate(
                    [
                        'mar_descripcion' => $batMarca
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                $batMarcaId = $marcaBateria->mar_id;


                /*
                 * 13. Buscar batería existente
                 */
                $bateria = Bateria::where(
                    'mar_id',
                    $batMarcaId
                )
                    ->where(
                        'bat_modelo',
                        $batModelo
                    )
                    ->where(
                        'bat_voltaje',
                        $batVoltaje
                    )
                    ->where(
                        'bat_capacidad',
                        $batCapacidad
                    )
                    ->first();


                /*
                 * 14. Crear batería si no existe
                 */
                if (!$bateria) {

                    $bateria = Bateria::create([
                        'bat_modelo' => $batModelo,
                        'bat_voltaje' => $batVoltaje,
                        'bat_capacidad' => $batCapacidad,
                        'mar_id' => $batMarcaId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }


                /*
                 * 15. Crear relación Batería-Equipo
                 */
                $existingRelation = BateriaEquipo::where(
                    'equ_id',
                    $equipo->equ_id
                )
                    ->where(
                        'bat_id',
                        $bateria->bat_id
                    )
                    ->exists();


                if (!$existingRelation) {

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


        /*
         * 16. Todo salió correctamente
         */
        return [
            'status' => 'success',
            'message' => 'Cliente y registros relacionados creados exitosamente.'
        ];
    }


    /**
     * Obtiene los registros con error o duplicados.
     */
    public function getErrors(Request $request)
    {
        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return response()->json([
                'error' => 'batch_id requerido'
            ], 400);
        }

        $errors = ExcelImportStaging::where(
            'import_batch_id',
            $batchId
        )
            ->whereIn(
                'import_status',
                ['error', 'duplicado']
            )
            ->select(
                'id',
                'eis_nombre_empresa_persona',
                'eis_identificacion',
                'import_status',
                'import_error'
            )
            ->get();

        return response()->json([
            'batchId' => $batchId,
            'errorCount' => $errors->count(),
            'errors' => $errors
        ], 200);
    }


    /**
     * Obtiene el estado general del batch.
     */
    public function getBatchStatus(Request $request)
    {
        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return response()->json([
                'error' => 'batch_id requerido'
            ], 400);
        }

        $stats = ExcelImportStaging::where(
            'import_batch_id',
            $batchId
        )
            ->selectRaw(
                'import_status, COUNT(*) as count'
            )
            ->groupBy('import_status')
            ->pluck('count', 'import_status');

        return response()->json([
            'batchId' => $batchId,

            'pendiente' =>
                $stats['pendiente'] ?? 0,

            'procesado' =>
                $stats['procesado'] ?? 0,

            'duplicado' =>
                $stats['duplicado'] ?? 0,

            'error' =>
                $stats['error'] ?? 0,

            'total' =>
                $stats->sum()
        ], 200);
    }


    /**
     * Descarga el CSV de resultados.
     */
    public function getImportResultsCsv(Request $request)
    {
        $batchId = $request->input('batch_id');

        if (!$batchId) {
            return response()->json([
                'error' => 'batch_id requerido'
            ], 400);
        }

        $stagingRecords = ExcelImportStaging::where(
            'import_batch_id',
            $batchId
        )
            ->whereIn(
                'import_status',
                ['procesado', 'duplicado', 'error']
            )
            ->get();

        if ($stagingRecords->isEmpty()) {
            return response()->json([
                'error' =>
                    'No se encontraron resultados para este batch'
            ], 404);
        }

        $results = [];
        $rowNumber = 2;

        foreach ($stagingRecords as $record) {

            /*
             * Traducimos el estado interno a un estado
             * entendible para el usuario final.
             */
            switch ($record->import_status) {

                case 'procesado':
                    $status = 'success';
                    break;

                case 'duplicado':
                    $status = 'duplicate';
                    break;

                default:
                    $status = 'error';
                    break;
            }

            $results[] = [
                'row' => $rowNumber,
                'status' => $status,
                'error' => $record->import_error,
                'data' => $record->toArray()
            ];

            $rowNumber++;
        }

        $filename =
            "resultados_batch_{$batchId}_"
            . date('Y-m-d_H-i-s')
            . '.csv';

        return $this->generateImportResultsCsv(
            $results,
            $filename
        );
    }
}
