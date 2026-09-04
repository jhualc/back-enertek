<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\ImportResultsTrait;

class StagingToClientController extends Controller
{
    use ImportResultsTrait;

    private function consoleLog(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    private function normalizeBatteryValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $normalized = strtolower(trim(preg_replace('/[^a-z0-9]+/', ' ', $text)));
        $placeholderValues = ['na', 'n a', 'no aplica', 'no aplica', 'none', 'null', 's n', 'sin dato', 'sin datos', '0', '-'];

        if (in_array($normalized, $placeholderValues, true) || preg_match('/^(na|n\/a|no aplica|no aplica|none|sin dato|sin datos|s\/n|x)$/', $normalized)) {
            return null;
        }

        return $text;
    }

    private function resolveClienteIdentificacion($record): string
    {
        $identificacion = trim((string) ($record->eis_identificacion ?? ''));

        if ($identificacion !== '') {
            return $identificacion;
        }

        throw new \InvalidArgumentException('Identificación de cliente requerida');
    }

    private function resolveTelefonoSede($record): ?string
    {
        $telefono = trim((string) ($record->eis_telefono_contacto_2 ?? ''));
        if ($telefono !== '') {
            return $telefono;
        }

        $telefono = trim((string) ($record->eis_telefono_contacto_1 ?? ''));
        if ($telefono !== '') {
            return $telefono;
        }

        return null;
    }

    /**
     * Procesa los registros pendientes en la tabla staging y los inserta/actualiza en la tabla de clientes.
     */
    
public function migrateClients(Request $request)
{
    set_time_limit(0);

    $batchId = $request->input('batch_id');
    $returnFormat = $request->input('format', 'csv');

    if (!$batchId) {
        return response()->json([
            'error' => 'El campo batch_id es obligatorio para filtrar la importación.'
        ], 400);
    }

    // Obtener únicamente registros pendientes del batch
    $stagingRecords = DB::table('excel_import_staging')
        ->where('import_batch_id', $batchId)
        ->where('import_status', 'pendiente')
        ->get();

    if ($stagingRecords->isEmpty()) {
        return response()->json([
            'message' => 'No se encontraron registros pendientes para procesar en este lote.'
        ], 404);
    }

    $results = [];
    $processed = 0;
    $duplicates = 0;
    $errors = 0;
    $rowNumber = 2;

    foreach ($stagingRecords as $record) {

        try {

            $identificacionCliente = $this->resolveClienteIdentificacion($record);

            $this->consoleLog('Procesando registro de staging', [
                'record_id' => $record->id,
                'identificacion' => $identificacionCliente,
                'batch_id' => $batchId,
            ]);

            /*
             * ============================================================
             * 1. VALIDAR SI EL CLIENTE YA EXISTE
             * ============================================================
             *
             * IMPORTANTE:
             * Si existe, NO hacemos updateOrInsert.
             * NO creamos sede.
             * NO creamos equipo.
             * NO creamos batería.
             * NO creamos relaciones.
             *
             * Simplemente clasificamos la fila como DUPLICADO.
             */

            $clienteExistente = DB::table('cliente')
                ->where('cli_identificacion', $identificacionCliente)
                ->first();

           if ($clienteExistente) {

    $mensajeDuplicado = "El cliente con identificación {$identificacionCliente} ya existe.";

    DB::table('excel_import_staging')
        ->where('id', $record->id)
        ->update([
            'import_status' => 'procesado',
            'import_error' => null,
            'updated_at' => now()
        ]);

    $results[] = [
        'row' => $rowNumber,
        'status' => 'duplicate',
        'error' => $mensajeDuplicado,
        'data' => (array) $record
    ];

    $rowNumber++;
    continue;
}


            /*
             * ============================================================
             * 2. CLIENTE NUEVO
             * ============================================================
             *
             * A partir de aquí sabemos que el cliente NO existe.
             */

            DB::transaction(function () use (
                $record,
                $identificacionCliente,
                &$processed
            ) {

                /*
                 * 2.1 Crear Cliente
                 */

                try {

                    $clienteId = DB::table('cliente')->insertGetId([
                        'cli_identificacion' =>
                            $identificacionCliente,

                        'cli_nombre' =>
                            $record->eis_nombre_empresa_persona
                            ?? 'CLIENTE SIN NOMBRE',

                        'cli_tipo_identificacion' =>
                            $record->eis_tipo_identificacion
                            ?? 'CC/NIT',

                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $this->consoleLog(
                        'Cliente creado exitosamente',
                        [
                            'record_id' => $record->id,
                            'cli_id' => $clienteId
                        ]
                    );

                } catch (\Exception $e) {

                    $this->consoleLog(
                        'ERROR al insertar cliente',
                        [
                            'record_id' => $record->id,
                            'error' => $e->getMessage()
                        ]
                    );

                    throw $e;
                }


                /*
                 * 2.2 Crear / obtener Marca del Equipo
                 */

                $marId = null;

                if (!empty($record->eis_marca_equipo)) {

                    try {

                        $marca = DB::table('marca')
                            ->where(
                                'mar_descripcion',
                                $record->eis_marca_equipo
                            )
                            ->first();

                        if ($marca) {

                            $marId = $marca->mar_id;

                        } else {

                            $marId = DB::table('marca')->insertGetId([
                                'mar_descripcion' =>
                                    $record->eis_marca_equipo,

                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        $this->consoleLog(
                            'Marca insertada/obtenida exitosamente',
                            [
                                'record_id' => $record->id,
                                'mar_id' => $marId
                            ]
                        );

                    } catch (\Exception $e) {

                        $this->consoleLog(
                            'ERROR al insertar marca',
                            [
                                'record_id' => $record->id,
                                'error' => $e->getMessage()
                            ]
                        );

                        throw $e;
                    }
                }


                /*
                 * 2.3 Crear / obtener Tipo de Equipo
                 */

                $teqId = null;

                $tipoEquipoDescripcion =
                    $record->eis_tipo_equipo ?? 'UPS';

                try {

                    $tipoEquipo = DB::table('tipo_equipo')
                        ->where(
                            'teq_descripcion',
                            $tipoEquipoDescripcion
                        )
                        ->first();

                    if ($tipoEquipo) {

                        $teqId = $tipoEquipo->teq_id;

                    } else {

                        $teqId = DB::table('tipo_equipo')->insertGetId([
                            'teq_descripcion' =>
                                $tipoEquipoDescripcion,

                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    $this->consoleLog(
                        'Tipo equipo insertado/obtenido exitosamente',
                        [
                            'record_id' => $record->id,
                            'teq_id' => $teqId
                        ]
                    );

                } catch (\Exception $e) {

                    $this->consoleLog(
                        'ERROR al insertar tipo equipo',
                        [
                            'record_id' => $record->id,
                            'error' => $e->getMessage()
                        ]
                    );

                    throw $e;
                }


                /*
                 * 2.4 Crear / obtener Sede
                 */

                $equipoId = null;
                $clsId = null;

                if (
                    !empty($record->eis_sede) &&
                    !empty($record->eis_direccion)
                ) {

                    $clienteSede = DB::table('cliente_sedes')
                        ->where('cli_id', $clienteId)
                        ->where(
                            'cls_descripcion',
                            $record->eis_sede
                        )
                        ->first();

                    if ($clienteSede) {

                        $clsId = $clienteSede->cls_id;

                    } else {

                        $clsId = DB::table('cliente_sedes')
                            ->insertGetId([
                                'cli_id' => $clienteId,

                                'cls_descripcion' =>
                                    $record->eis_sede,

                                'cls_direccion' =>
                                    $record->eis_direccion,

                                'cls_ciudad' =>
                                    $record->eis_ciudad,

                                'cls_departamento' =>
                                    $record->eis_departamento,

                                'cls_telefono' =>
                                    $this->resolveTelefonoSede($record),

                                'cls_correo' =>
                                    $record->eis_correo_contacto_2
                                    ?? $record->eis_correo_contacto_1,

                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }
                }


                /*
                 * 2.5 Crear / actualizar Equipo
                 */

                if (
                    !empty($record->eis_modelo_equipo) &&
                    !empty($record->eis_serial_equipo) &&
                    $marId &&
                    $teqId
                ) {

                    try {

                        $equipo = DB::table('equipo')
                            ->where(
                                'equ_serial',
                                $record->eis_serial_equipo
                            )
                            ->first();

                        $equipoData = [
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

                            'mar_id' => $marId,

                            'teq_id' => $teqId,

                            'cls_id' => $clsId,

                            'updated_at' => now(),
                        ];

                        if ($equipo) {

                            $equipoId = $equipo->equ_id;

                            DB::table('equipo')
                                ->where('equ_id', $equipoId)
                                ->update($equipoData);

                        } else {

                            $equipoData['created_at'] = now();

                            $equipoId = DB::table('equipo')
                                ->insertGetId($equipoData);
                        }

                        $this->consoleLog(
                            'Equipo insertado/actualizado exitosamente',
                            [
                                'record_id' => $record->id,
                                'equ_id' => $equipoId
                            ]
                        );

                    } catch (\Exception $e) {

                        $this->consoleLog(
                            'ERROR al insertar equipo',
                            [
                                'record_id' => $record->id,
                                'error' => $e->getMessage()
                            ]
                        );

                        throw $e;
                    }
                }


                /*
                 * 2.6 Relación Cliente - Equipo
                 */

                if (!empty($equipoId)) {

                    DB::table('cliente_equipo')->insertOrIgnore([
                        'cli_id' => $clienteId,
                        'equ_id' => $equipoId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $this->consoleLog(
                        'Relación cliente-equipo creada',
                        [
                            'record_id' => $record->id
                        ]
                    );


                    /*
                     * 2.7 Batería
                     */

                    $batMarca =
                        $this->normalizeBatteryValue(
                            $record->eis_marca_bateria
                        );

                    $batModelo =
                        $this->normalizeBatteryValue(
                            $record->eis_referencia_bateria
                        );

                    $batVoltaje =
                        $this->normalizeBatteryValue(
                            $record->eis_voltaje_bateria
                        );

                    $batCapacidad =
                        $this->normalizeBatteryValue(
                            $record->eis_amperaje_bateria
                        );

                    if (
                        $batMarca &&
                        $batModelo &&
                        $batVoltaje &&
                        $batCapacidad
                    ) {

                        $marcaBateria = DB::table('marca')
                            ->where(
                                'mar_descripcion',
                                $batMarca
                            )
                            ->first();

                        if ($marcaBateria) {

                            $batMarcaId = $marcaBateria->mar_id;

                        } else {

                            $batMarcaId = DB::table('marca')
                                ->insertGetId([
                                    'mar_descripcion' => $batMarca,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                        }

                        $bateria = DB::table('bateria')
                            ->where(
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

                        if ($bateria) {

                            $bateriaId = $bateria->bat_id;

                        } else {

                            $bateriaId = DB::table('bateria')
                                ->insertGetId([
                                    'bat_modelo' => $batModelo,
                                    'bat_voltaje' => $batVoltaje,
                                    'bat_capacidad' => $batCapacidad,
                                    'mar_id' => $batMarcaId,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                        }


                        /*
                         * Relación Batería - Equipo
                         */

                        $existingRelation = DB::table('bateria_equipo')
                            ->where('equ_id', $equipoId)
                            ->where('bat_id', $bateriaId)
                            ->exists();

                        if (!$existingRelation) {

                            DB::table('bateria_equipo')->insert([
                                'equ_id' => $equipoId,
                                'bat_id' => $bateriaId,
                                'beq_fecha' => now()->toDateString(),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }


                /*
                 * 2.8 Actualizar / crear Sede con información completa
                 */

                DB::table('cliente_sedes')->updateOrInsert(
                    [
                        'cli_id' => $clienteId,

                        'cls_descripcion' =>
                            $record->eis_sede
                            ?? 'Sede principal',
                    ],
                    [
                        'cls_direccion' =>
                            $record->eis_direccion,

                        'cls_departamento' =>
                            $record->eis_departamento,

                        'cls_ciudad' =>
                            $record->eis_ciudad,

                        'cls_telefono' =>
                            $this->resolveTelefonoSede($record),

                        'cls_correo' =>
                            $record->eis_correo_contacto_2
                            ?? $record->eis_correo_contacto_1,

                        'updated_at' => now(),
                    ]
                );


                /*
                 * 2.9 Marcar staging como procesado
                 */

                DB::table('excel_import_staging')
                    ->where('id', $record->id)
                    ->update([
                        'import_status' => 'procesado',
                        'import_error' => null,
                        'updated_at' => now()
                    ]);

                $processed++;

            });

            /*
             * Resultado exitoso
             */

            $results[] = [
                'row' => $rowNumber,
                'status' => 'success',
                'error' => null,
                'data' => (array) $record
            ];

        } catch (\Exception $e) {

            $errors++;

            $this->consoleLog(
                'ERROR al procesar registro',
                [
                    'record_id' => $record->id ?? 'N/A',
                    'error' => $e->getMessage()
                ]
            );

            DB::table('excel_import_staging')
                ->where('id', $record->id)
                ->update([
                    'import_status' => 'error',
                    'import_error' => $e->getMessage(),
                    'updated_at' => now()
                ]);

            $results[] = [
                'row' => $rowNumber,
                'status' => 'error',
                'error' => $e->getMessage(),
                'data' => (array) $record
            ];
        }

        $rowNumber++;
    }


    /*
     * Resumen
     */

    $summary = $this->generateImportSummary($results);

    if ($returnFormat === 'json') {

        return response()->json([
            'message' => 'Migración de clientes finalizada.',
            'summary' => $summary,
            'results' => $results
        ], 200);
    }


    /*
     * CSV
     */

    $filename =
        "migracion_batch_{$batchId}_"
        . date('Y-m-d_H-i-s')
        . '.csv';

    return $this->generateImportResultsCsv(
        $results,
        $filename
    );
}

}
