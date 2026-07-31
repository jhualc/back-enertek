<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientFullImportController extends Controller
{
    private function consoleLog(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    private function normalizeHeader($value): string
    {
        if ($value === null) {
            return '';
        }

        $text = (string) $value;

        if (function_exists('iconv')) {
            $text = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        }

        $text = strtolower(trim(preg_replace('/[^a-z0-9]+/', ' ', $text)));
        $text = preg_replace('/\b([a-z]+)s\b/', '$1', $text);

        return $text;
    }

    private function containsAllWords(string $haystack, string $candidate): bool
    {
        $stopWords = ['de', 'del', 'la', 'el', 'y', 'of', 'the', 'para', 'por', 'con', 'a', 'al', 'en'];
        $terms = preg_split('/\s+/', $this->normalizeHeader($candidate));

        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            if (in_array($term, $stopWords, true)) {
                continue;
            }

            if (!str_contains($haystack, $term)) {
                return false;
            }
        }

        return true;
    }

    private function findBatteryColumns(array $headers): array
    {
        $result = [
            'marca' => null,
            'referencia' => null,
            'voltaje' => null,
            'amperaje' => null,
        ];

        foreach ($headers as $index => $header) {
            if ($header === null || trim((string) $header) === '') {
                continue;
            }

            $headerLower = strtolower(trim((string) $header));
            
            // Buscar por contención de palabras clave simples
            if (str_contains($headerLower, 'marca') && str_contains($headerLower, 'bater')) {
                $result['marca'] = $index;
            } elseif (str_contains($headerLower, 'referencia')) {
                $result['referencia'] = $index;
            } elseif (str_contains($headerLower, 'voltaje')) {
                $result['voltaje'] = $index;
            } elseif (str_contains($headerLower, 'amperaje')) {
                $result['amperaje'] = $index;
            }
        }

        return $result;
    }

    private function findColumnIndex(array $headers, array $candidates): ?int
    {
        foreach ($headers as $index => $header) {
            if ($header === null || trim((string) $header) === '') {
                continue;
            }
            
            $headerLower = strtolower(trim((string) $header));
            
            foreach ($candidates as $candidate) {
                $candidateLower = strtolower($candidate);
                // Buscar coincidencia exacta o por contención simple
                if ($headerLower === $candidateLower || str_contains($headerLower, $candidateLower)) {
                    return $index;
                }
            }
        }

        return null;
    }

    private function getCellValue(array $row, ?int $index)
    {
        if ($index === null || !array_key_exists($index, $row)) {
            return null;
        }

        return $row[$index];
    }

    private function normalizeNumericCell($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $cleanValue = trim((string) $value);
        if ($cleanValue === '') {
            return null;
        }

        $cleanValue = preg_replace('/[^0-9,\-\.]/', '', $cleanValue);

        if ($cleanValue === '' || !is_numeric(str_replace(',', '.', $cleanValue))) {
            return null;
        }

        return (int) str_replace(',', '.', $cleanValue);
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

        $normalized = strtolower($this->normalizeHeader($text));

        $placeholderValues = ['na', 'n a', 'n a', 'no aplica', 'no aplica', 'none', 'null', 's n', 'sin dato', 'sin datos', '0', '-'];
        if (in_array($normalized, $placeholderValues, true) || preg_match('/^(na|n\/a|no aplica|no aplica|none|sin dato|sin datos|s\/n|x)$/', $normalized)) {
            return null;
        }

        return $text;
    }

    private function getRowsFromWorksheet($worksheet): array
    {
        $rows = $worksheet->toArray();
        $firstNonEmptyIndex = null;

        foreach ($rows as $index => $row) {
            $hasContent = false;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $hasContent = true;
                    break;
                }
            }

            if ($hasContent) {
                $firstNonEmptyIndex = $index;
                break;
            }
        }

        if ($firstNonEmptyIndex === null) {
            return [];
        }

        return array_slice($rows, $firstNonEmptyIndex);
    }

    private function getDataRowsFromSpreadsheet($spreadsheet): array
    {
        $sheetNames = $spreadsheet->getSheetNames();

        foreach ($sheetNames as $sheetName) {
            $worksheet = $spreadsheet->getSheetByName($sheetName);
            if (!$worksheet) {
                continue;
            }

            $rows = $this->getRowsFromWorksheet($worksheet);
            if (count($rows) > 1) {
                return $rows;
            }
        }

        $activeSheet = $spreadsheet->getActiveSheet();
        return $this->getRowsFromWorksheet($activeSheet);
    }

    private function getHeaderAndDataRows(array $rows): array
    {
        // Si tenemos al menos 2 filas, la primera es header y el resto son datos
        if (count($rows) >= 1) {
            $header = $rows[0];
            $dataRows = array_slice($rows, 1);
            return [$header, $dataRows];
        }
        
        return [[], []];
    }

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
        $rows = $this->getDataRowsFromSpreadsheet($spreadsheet);

        $this->consoleLog('Inicio de carga de Excel a staging', [
            'rows_count' => count($rows),
        ]);

        // Verifica que el archivo tiene datos (encabezado + al menos una fila)
        if (count($rows) <= 1) {
            $this->consoleLog('El archivo está vacío o no tiene datos válidos');
            return response()->json(['error' => 'El archivo está vacío o no tiene datos válidos'], 400);
        }

        [$header, $dataRows] = $this->getHeaderAndDataRows($rows);

        $headerFiltered = array_values(array_filter($header, function ($value) {
            return $value !== null && trim((string) $value) !== '';
        }));

        $this->consoleLog('Encabezado detectado para Excel', [
            'header' => $headerFiltered,
            'header_count' => count($headerFiltered),
            'total_columns_in_row' => count($header),
        ]);

        // Detectar índices de columna por nombre en la fila de encabezados (si existe)
        $sectorEmpresaIndex = $this->findColumnIndex($header, ['sector empresa', 'sector', 'sector empresa', 'sector_empresa']);
        $tipoClienteIndex = $this->findColumnIndex($header, ['tipo cliente', 'tipo de cliente', 'tipo_cliente']);
        $siglaIndex = $this->findColumnIndex($header, ['sigla']);
        $nombreEmpresaIndex = $this->findColumnIndex($header, ['nombre empresa', 'nombre empresa persona', 'nombre empresa persona', 'nombre_empresa_persona']);
        $tipoIdentificacionIndex = $this->findColumnIndex($header, ['tipo identificacion', 'tipo de identificacion', 'tipo_identificacion']);
        $identificacionIndex = $this->findColumnIndex($header, ['identificacion']);
        $dvIndex = $this->findColumnIndex($header, ['dv']);
        $departamentoIndex = $this->findColumnIndex($header, ['departamento']);
        $ciudadIndex = $this->findColumnIndex($header, ['ciudad']);
        $direccionIndex = $this->findColumnIndex($header, ['direccion']);
        $sedeIndex = $this->findColumnIndex($header, ['sede']);
        $ubicacionEquipoIndex = $this->findColumnIndex($header, ['ubicacion equipo', 'ubicacion del equipo', 'ubicacion_equipo']);
        $nombreContacto1Index = $this->findColumnIndex($header, ['nombre contacto 1', 'contacto 1']);
        $correoContacto1Index = $this->findColumnIndex($header, ['correo contacto 1', 'correo 1']);
        $telefonoContacto1Index = $this->findColumnIndex($header, ['telefono contacto 1', 'telefono 1']);
        $nombreContacto2Index = $this->findColumnIndex($header, ['nombre contacto 2', 'contacto 2']);
        $correoContacto2Index = $this->findColumnIndex($header, ['correo contacto 2', 'correo 2']);
        $telefonoContacto2Index = $this->findColumnIndex($header, ['telefono contacto 2', 'telefono 2']);
        $estadoClienteIndex = $this->findColumnIndex($header, ['estado cliente', 'estado']);
        $tipoRelacionComercialIndex = $this->findColumnIndex($header, ['tipo relacion comercial', 'tipo de relacion comercial', 'tipo_relacion_comercial']);
        $marcaEquipoIndex = $this->findColumnIndex($header, ['marca equipo', 'marca del equipo', 'marca_equipo', 'marca de equipo']);
        $tipoEquipoIndex = $this->findColumnIndex($header, ['tipo equipo', 'tipo de equipo', 'tipo_equipo']);
        $modeloEquipoIndex = $this->findColumnIndex($header, ['modelo equipo', 'modelo del equipo', 'modelo_equipo', 'modelo de equipo']);
        $potenciaIndex = $this->findColumnIndex($header, ['potencia', 'potencia kva', 'potencia kva', 'potencia_kva']);
        $serialIndex = $this->findColumnIndex($header, ['serial', 'serial equipo', 'serial_equipo']);
        $cantidadBateriasIntIndex = $this->findColumnIndex($header, ['cantidad baterias int', 'cantidad de baterias int', 'cantidad de baterías int', 'cantidad_baterias_int', 'cant baterias int']);
        $cantidadBateriasExtIndex = $this->findColumnIndex($header, ['cantidad baterias ext', 'cantidad de baterias ext', 'cantidad de baterías ext', 'cantidad_baterias_ext', 'cant baterias ext']);
        
        // Detectar columnas de batería con método especializado
        $batteryColumns = $this->findBatteryColumns($header);
        $marcaBateriaIndex = $batteryColumns['marca'];
        $referenciaBateriaIndex = $batteryColumns['referencia'];
        $voltajeBateriaIndex = $batteryColumns['voltaje'];
        $amperajeBateriaIndex = $batteryColumns['amperaje'];
        
        $snmpsIndex = $this->findColumnIndex($header, ['snmps']);

        // Log de índices detectados para las columnas de batería
        $this->consoleLog('Índices de columnas detectados para batería', [
            'marcaBateriaIndex' => $marcaBateriaIndex,
            'referenciaBateriaIndex' => $referenciaBateriaIndex,
            'voltajeBateriaIndex' => $voltajeBateriaIndex,
            'amperajeBateriaIndex' => $amperajeBateriaIndex,
        ]);

        // Procesar los datos
        $insertData = [];
        $batchId = time(); // Identificador temporal para el lote de importación

        // Log de las primeras 3 filas de datos (antes de procesar)
        $previewLimit = min(3, count($dataRows));
        for ($i = 0; $i < $previewLimit; $i++) {
            $row = $dataRows[$i];
            $this->consoleLog("Preview de fila $i de datos Excel", [
                'marca_bateria_index_' . ($marcaBateriaIndex ?? 'null') => $this->getCellValue($row, $marcaBateriaIndex),
                'referencia_bateria_index_' . ($referenciaBateriaIndex ?? 'null') => $this->getCellValue($row, $referenciaBateriaIndex),
                'voltaje_bateria_index_' . ($voltajeBateriaIndex ?? 'null') => $this->getCellValue($row, $voltajeBateriaIndex),
                'amperaje_bateria_index_' . ($amperajeBateriaIndex ?? 'null') => $this->getCellValue($row, $amperajeBateriaIndex),
            ]);
        }

        // Mostrar todas las columnas del encabezado (para referencia)
        $headerWithIndex = [];
        foreach ($header as $idx => $headerVal) {
            $headerWithIndex["col_$idx"] = $headerVal;
        }
        $this->consoleLog('Todas las columnas del encabezado (con índice)', $headerWithIndex);

        // Mostrar todas las columnas de la primera fila de datos (para referencia)
        if (count($dataRows) > 0) {
            $firstRowWithIndex = [];
            foreach ($dataRows[0] as $idx => $cellVal) {
                $firstRowWithIndex["col_$idx"] = $cellVal;
            }
            $this->consoleLog('Todas las columnas de la primera fila de datos (con índice)', $firstRowWithIndex);
        }

        foreach ($dataRows as $row) {
            // Mapeo de columnas según la migración excel_import_staging
            $insertData[] = [
                'eis_sector_empresa'          => $this->getCellValue($row, $sectorEmpresaIndex ?? 0) ?? null,
                'eis_tipo_cliente'            => $this->getCellValue($row, $tipoClienteIndex ?? 1) ?? null,
                'eis_sigla'                   => $this->getCellValue($row, $siglaIndex ?? 2) ?? null,
                'eis_nombre_empresa_persona'  => $this->getCellValue($row, $nombreEmpresaIndex ?? 3) ?? null,
                'eis_tipo_identificacion'     => $this->getCellValue($row, $tipoIdentificacionIndex ?? 4) ?? null,
                'eis_identificacion'          => $this->getCellValue($row, $identificacionIndex ?? 5) ?? null,
                'eis_dv'                      => $this->getCellValue($row, $dvIndex ?? 6) ?? null,
                'eis_departamento'            => $this->getCellValue($row, $departamentoIndex ?? 7) ?? null,
                'eis_ciudad'                  => $this->getCellValue($row, $ciudadIndex ?? 8) ?? null,
                'eis_direccion'               => $this->getCellValue($row, $direccionIndex ?? 9) ?? null,
                'eis_sede'                    => $this->getCellValue($row, $sedeIndex ?? 10) ?? null,
                'eis_ubicacion_equipo'        => $this->getCellValue($row, $ubicacionEquipoIndex ?? 11) ?? null,
                'eis_nombre_contacto_1'       => $this->getCellValue($row, $nombreContacto1Index ?? 12) ?? null,
                'eis_correo_contacto_1'       => $this->getCellValue($row, $correoContacto1Index ?? 13) ?? null,
                'eis_telefono_contacto_1'     => $this->getCellValue($row, $telefonoContacto1Index ?? 14) ?? null,
                'eis_nombre_contacto_2'       => $this->getCellValue($row, $nombreContacto2Index ?? 15) ?? null,
                'eis_correo_contacto_2'       => $this->getCellValue($row, $correoContacto2Index ?? 16) ?? null,
                'eis_telefono_contacto_2'     => $this->getCellValue($row, $telefonoContacto2Index ?? 17) ?? null,
                'eis_estado_cliente'          => $this->getCellValue($row, $estadoClienteIndex ?? 18) ?? null,
                'eis_tipo_relacion_comercial' => $this->getCellValue($row, $tipoRelacionComercialIndex ?? 19) ?? null,
                'eis_marca_equipo'            => $this->getCellValue($row, $marcaEquipoIndex ?? 20) ?? null,
                'eis_tipo_equipo'             => $this->getCellValue($row, $tipoEquipoIndex ?? 21) ?? null,
                'eis_modelo_equipo'           => $this->getCellValue($row, $modeloEquipoIndex ?? 22) ?? null,
                'eis_potencia_kva'            => $this->getCellValue($row, $potenciaIndex ?? 23) ?? null,
                'eis_serial_equipo'           => $this->getCellValue($row, $serialIndex ?? 24) ?? null,
                'eis_cantidad_baterias_int'   => $this->normalizeNumericCell($this->getCellValue($row, $cantidadBateriasIntIndex ?? 25)),
                'eis_cantidad_baterias_ext'   => $this->normalizeNumericCell($this->getCellValue($row, $cantidadBateriasExtIndex ?? 26)),
                'eis_marca_bateria'           => $this->normalizeBatteryValue($this->getCellValue($row, $marcaBateriaIndex ?? 27)),
                'eis_referencia_bateria'      => $this->normalizeBatteryValue($this->getCellValue($row, $referenciaBateriaIndex ?? 28)),
                'eis_voltaje_bateria'         => $this->normalizeBatteryValue($this->getCellValue($row, $voltajeBateriaIndex ?? 29)),
                'eis_amperaje_bateria'        => $this->normalizeBatteryValue($this->getCellValue($row, $amperajeBateriaIndex ?? 30)),
                'eis_snmps'                   => $this->getCellValue($row, $snmpsIndex ?? 31) ?? null,
                'import_status'               => 'pendiente',
                'import_batch_id'             => $batchId,
                'created_at'                  => now(),
                'updated_at'                  => now()
            ];
        }

        // Insertar en la tabla staging (preparación)
        if (!empty($insertData)) {
            DB::table('excel_import_staging')->insert($insertData);
            $this->consoleLog('Fin de carga de Excel a staging', [
                'inserted_rows' => count($insertData),
                'batch_id' => $batchId,
            ]);
            
            // Preparar datos de debug para la respuesta
            $debugInfo = [
                'header_count' => count($headerFiltered),
                'header' => $headerFiltered,
                'first_row_data' => [],
            ];
            
            if (count($dataRows) > 0) {
                foreach ($dataRows[0] as $idx => $val) {
                    $debugInfo['first_row_data']["col_$idx"] = $val;
                }
            }
            
            $debugInfo['column_indices_detected'] = [
                'marcaBateriaIndex' => $marcaBateriaIndex,
                'referenciaBateriaIndex' => $referenciaBateriaIndex,
                'voltajeBateriaIndex' => $voltajeBateriaIndex,
                'amperajeBateriaIndex' => $amperajeBateriaIndex,
            ];
            
            return response()->json([
                'message' => 'Datos cargados exitosamente en el área de preparación',
                'batch_id' => $batchId,
                'count' => count($insertData),
                'debug' => $debugInfo,
            ], 200);
        }

        return response()->json(['error' => 'No se pudieron procesar los datos del archivo'], 500);
    }
}
