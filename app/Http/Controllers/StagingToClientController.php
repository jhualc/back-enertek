<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StagingToClientController extends Controller
{
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

        $nombre = trim((string) ($record->eis_nombre_empresa_persona ?? 'cliente'));
        $nombreBase = $nombre !== '' ? preg_replace('/[^A-Za-z0-9]+/', '_', $nombre) : 'cliente';
        $nombreBase = trim($nombreBase, '_');

        return 'SIN_IDENTIFICACION_' . ($nombreBase !== '' ? $nombreBase : 'cliente') . '_' . ($record->id ?? '0');
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
                $identificacionCliente = $this->resolveClienteIdentificacion($record);

                $this->consoleLog('Procesando registro de staging', [
                    'record_id' => $record->id,
                    'identificacion' => $identificacionCliente,
                    'batch_id' => $batchId,
                ]);

                DB::transaction(function () use ($record, $identificacionCliente, &$processed) {
                    // 1. Insertar o actualizar el Cliente en la tabla final
                    try {
                        DB::table('cliente')->updateOrInsert(
                            ['cli_identificacion' => $identificacionCliente],
                            [
                                'cli_nombre' => $record->eis_nombre_empresa_persona ?? 'CLIENTE SIN NOMBRE',
                                'cli_tipo_identificacion' => $record->eis_tipo_identificacion ?? 'CC/NIT',
                                'updated_at' => now(),
                                'created_at' => now()
                            ]
                        );
                        $this->consoleLog('Cliente insertado/actualizado exitosamente', ['record_id' => $record->id]);
                    } catch (\Exception $e) {
                        $this->consoleLog('ERROR al insertar cliente', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                        throw $e;
                    }

                    $clienteId = DB::table('cliente')
                        ->where('cli_identificacion', $identificacionCliente)
                        ->value('cli_id');

                    if (!$clienteId) {
                        throw new \Exception('No se pudo obtener el cliente insertado/actualizado.');
                    }

                    // 2. Insertar o crear la Marca del Equipo si no existe
                    $marId = null;
                    if (!empty($record->eis_marca_equipo)) {
                        try {
                            $marca = DB::table('marca')
                                ->where('mar_descripcion', $record->eis_marca_equipo)
                                ->first();

                            if ($marca) {
                                $marId = $marca->mar_id;
                            } else {
                                $marId = DB::table('marca')->insertGetId([
                                    'mar_descripcion' => $record->eis_marca_equipo,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                            $this->consoleLog('Marca insertada/obtenida exitosamente', ['record_id' => $record->id, 'mar_id' => $marId]);
                        } catch (\Exception $e) {
                            $this->consoleLog('ERROR al insertar marca', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                            throw $e;
                        }
                    }

                    // 3. Insertar o crear el tipo de equipo si no existe (usar valor desde staging si está disponible)
                    $teqId = null;
                    $tipoEquipoDescripcion = $record->eis_tipo_equipo ?? 'UPS';
                    try {
                        $tipoEquipo = DB::table('tipo_equipo')
                            ->where('teq_descripcion', $tipoEquipoDescripcion)
                            ->first();

                        if ($tipoEquipo) {
                            $teqId = $tipoEquipo->teq_id;
                        } else {
                            $teqId = DB::table('tipo_equipo')->insertGetId([
                                'teq_descripcion' => $tipoEquipoDescripcion,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                        $this->consoleLog('Tipo equipo insertado/obtenido exitosamente', ['record_id' => $record->id, 'teq_id' => $teqId]);
                    } catch (\Exception $e) {
                        $this->consoleLog('ERROR al insertar tipo equipo', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                        throw $e;
                    }

                    // 4. Insertar o actualizar el Equipo si hay modelo y serial
                    $equipoId = null;
                    $clsId = null;
                    if (!empty($record->eis_sede) && !empty($record->eis_direccion)) {
                        $clienteSede = DB::table('cliente_sedes')
                            ->where('cli_id', $clienteId)
                            ->where('cls_descripcion', $record->eis_sede)
                            ->first();

                        if ($clienteSede) {
                            $clsId = $clienteSede->cls_id;
                        } else {
                            $clsId = DB::table('cliente_sedes')->insertGetId([
                                'cli_id' => $clienteId,
                                'cls_descripcion' => $record->eis_sede,
                                'cls_direccion' => $record->eis_direccion,
                                'cls_ciudad' => $record->eis_ciudad,
                                'cls_departamento' => $record->eis_departamento,
                                'cls_telefono' => $this->resolveTelefonoSede($record),
                                'cls_correo' => $record->eis_correo_contacto_2 ?? $record->eis_correo_contacto_1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    if (!empty($record->eis_modelo_equipo) && !empty($record->eis_serial_equipo) && $marId && $teqId) {
                        try {
                            $equipo = DB::table('equipo')
                                ->where('equ_serial', $record->eis_serial_equipo)
                                ->first();

                            $equipoData = [
                                'equ_modelo' => $record->eis_modelo_equipo,
                                'equ_serial' => $record->eis_serial_equipo,
                                'equ_potencia' => $record->eis_potencia_kva,
                                'equ_ubicacion' => $record->eis_ubicacion_equipo,
                                'equ_cant_baterias' => ($record->eis_cantidad_baterias_int ?? 0) + ($record->eis_cantidad_baterias_ext ?? 0),
                                'mar_id' => $marId,
                                'teq_id' => $teqId,
                                'cls_id' => $clsId,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ];

                            if ($equipo) {
                                $equipoId = $equipo->equ_id;
                                DB::table('equipo')
                                    ->where('equ_id', $equipoId)
                                    ->update($equipoData);
                            } else {
                                $equipoId = DB::table('equipo')->insertGetId($equipoData);
                            }
                            $this->consoleLog('Equipo insertado/actualizado exitosamente', ['record_id' => $record->id, 'equ_id' => $equipoId]);
                        } catch (\Exception $e) {
                            $this->consoleLog('ERROR al insertar equipo', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                            throw $e;
                        }
                    }

                    // 5. Crear relación Cliente-Equipo
                    if (!empty($equipoId)) {
                        try {
                            DB::table('cliente_equipo')->insertOrIgnore([
                                'cli_id' => $clienteId,
                                'equ_id' => $equipoId,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $this->consoleLog('Relación cliente-equipo creada', ['record_id' => $record->id]);
                        } catch (\Exception $e) {
                            $this->consoleLog('ERROR al crear relación cliente-equipo', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                            throw $e;
                        }

                        // 6. Insertar o reutilizar la Batería asociada si hay marca, referencia, voltaje y amperaje
                        $batMarca = $this->normalizeBatteryValue($record->eis_marca_bateria);
                        $batModelo = $this->normalizeBatteryValue($record->eis_referencia_bateria);
                        $batVoltaje = $this->normalizeBatteryValue($record->eis_voltaje_bateria);
                        $batCapacidad = $this->normalizeBatteryValue($record->eis_amperaje_bateria);

                        if ($batMarca && $batModelo && $batVoltaje && $batCapacidad) {
                            try {
                                $marcaBateria = DB::table('marca')
                                    ->where('mar_descripcion', $batMarca)
                                    ->first();

                                if ($marcaBateria) {
                                    $batMarcaId = $marcaBateria->mar_id;
                                } else {
                                    $batMarcaId = DB::table('marca')->insertGetId([
                                        'mar_descripcion' => $batMarca,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }

                                $bateria = DB::table('bateria')
                                    ->where('mar_id', $batMarcaId)
                                    ->where('bat_modelo', $batModelo)
                                    ->where('bat_voltaje', $batVoltaje)
                                    ->where('bat_capacidad', $batCapacidad)
                                    ->first();

                                if ($bateria) {
                                    $bateriaId = $bateria->bat_id;
                                    $this->consoleLog('Batería existente reutilizada', [
                                        'record_id' => $record->id,
                                        'bat_id' => $bateriaId,
                                        'marca_bateria' => $batMarca,
                                        'bat_modelo' => $batModelo,
                                        'bat_voltaje' => $batVoltaje,
                                        'bat_capacidad' => $batCapacidad,
                                    ]);
                                } else {
                                    $bateriaId = DB::table('bateria')->insertGetId([
                                        'bat_modelo' => $batModelo,
                                        'bat_voltaje' => $batVoltaje,
                                        'bat_capacidad' => $batCapacidad,
                                        'mar_id' => $batMarcaId,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                    $this->consoleLog('Nueva batería creada', [
                                        'record_id' => $record->id,
                                        'bat_id' => $bateriaId,
                                        'marca_bateria' => $batMarca,
                                        'bat_modelo' => $batModelo,
                                        'bat_voltaje' => $batVoltaje,
                                        'bat_capacidad' => $batCapacidad,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                $this->consoleLog('ERROR al insertar/buscar batería', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                                throw $e;
                            }

                            try {
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
                                        'updated_at' => now(),
                                    ]);
                                    $this->consoleLog('Relación batería-equipo creada', [
                                        'record_id' => $record->id,
                                        'equ_id' => $equipoId,
                                        'bat_id' => $bateriaId,
                                    ]);
                                } else {
                                    $this->consoleLog('Relación batería-equipo ya existente', [
                                        'record_id' => $record->id,
                                        'equ_id' => $equipoId,
                                        'bat_id' => $bateriaId,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                $this->consoleLog('ERROR al crear relación batería-equipo', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                                throw $e;
                            }
                        } else {
                            $this->consoleLog('No se cargó batería porque faltan datos completos o son valores placeholder', [
                                'record_id' => $record->id,
                                'eis_marca_bateria' => $record->eis_marca_bateria,
                                'eis_referencia_bateria' => $record->eis_referencia_bateria,
                                'eis_voltaje_bateria' => $record->eis_voltaje_bateria,
                                'eis_amperaje_bateria' => $record->eis_amperaje_bateria,
                            ]);
                        }
                    }

                    // 7. Insertar o actualizar la sede del cliente con datos de staging
                    try {
                        DB::table('cliente_sedes')->updateOrInsert(
                            [
                                'cli_id' => $clienteId,
                                'cls_descripcion' => $record->eis_sede ?? 'Sede principal',
                            ],
                            [
                                'cls_direccion' => $record->eis_direccion,
                                'cls_departamento' => $record->eis_departamento,
                                'cls_ciudad' => $record->eis_ciudad,
                                'cls_telefono' => $this->resolveTelefonoSede($record),
                                'cls_correo' => $record->eis_correo_contacto_2 ?? $record->eis_correo_contacto_1,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                        $this->consoleLog('Sede cliente insertada/actualizada exitosamente', ['record_id' => $record->id]);
                    } catch (\Exception $e) {
                        $this->consoleLog('ERROR al insertar sede cliente', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                        throw $e;
                    }

                    // 8. Marcar el registro como procesado en la tabla staging
                    try {
                        DB::table('excel_import_staging')
                            ->where('id', $record->id)
                            ->update([
                                'import_status' => 'procesado',
                                'import_error' => null,
                                'updated_at' => now()
                            ]);
                        $this->consoleLog('Registro de staging marcado como procesado', ['record_id' => $record->id]);
                    } catch (\Exception $e) {
                        $this->consoleLog('ERROR al actualizar staging', ['record_id' => $record->id, 'error' => $e->getMessage()]);
                        throw $e;
                    }

                    $this->consoleLog('Registro de staging procesado correctamente (transacción completada)', [
                        'record_id' => $record->id,
                        'cliente_id' => $clienteId,
                        'equipo_id' => $equipoId,
                    ]);
                    
                    $processed++;
                });

            } catch (\Exception $e) {
                $errors++;

                try {
                    DB::reconnect();
                    DB::table('excel_import_staging')->where('id', $record->id)->update([
                        'import_status' => 'error',
                        'import_error' => $e->getMessage(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $innerException) {
                    \Log::error('Fallo al actualizar el estado de staging tras error: ' . $innerException->getMessage(), [
                        'record_id' => $record->id,
                        'original_error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Migración de clientes finalizada.',
            'summary' => ['procesados' => $processed, 'errores' => $errors]
        ], 200);
    }
}
