<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ImportResultsTrait
{
    /**
     * Genera un archivo CSV con los resultados de importación
     * 
     * @param array $results Array de registros con estructura ['row' => n, 'status' => 'success|error', 'error' => 'mensaje', 'data' => [...]]
     * @param string $filename Nombre del archivo CSV
     * @return StreamedResponse Respuesta con el archivo CSV
     */
    public function generateImportResultsCsv(array $results, string $filename = 'resultados_importacion.csv'): StreamedResponse
    {
        return response()->stream(function () use ($results) {
            $stream = fopen('php://output', 'w');
            
            // Configurar para UTF-8 con BOM (importante para Excel)
            fprintf($stream, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados del CSV
            $headers = [
                'Fila',
                'Estado',
                'Razón del Error',
                'Identificación',
                'Nombre',
                'Tipo Identificación',
                'Sede',
                'Marca Equipo',
                'Modelo Equipo',
                'Serial Equipo',
            ];
            
            fputcsv($stream, $headers, ',', '"');
            
            // Escribir datos
            foreach ($results as $result) {
                $data = $result['data'] ?? [];
                
                $row = [
                    $result['row'] ?? '',
                    $result['status'] === 'success' ? 'Exitoso' : 'Error',
                    $result['status'] === 'error' ? ($result['error'] ?? 'Error sin descripción') : '',
                    $data['eis_identificacion'] ?? $data['cli_identificacion'] ?? '',
                    $data['eis_nombre_empresa_persona'] ?? $data['cli_nombre'] ?? '',
                    $data['eis_tipo_identificacion'] ?? $data['cli_tipo_identificacion'] ?? '',
                    $data['eis_sede'] ?? '',
                    $data['eis_marca_equipo'] ?? '',
                    $data['eis_modelo_equipo'] ?? '',
                    $data['eis_serial_equipo'] ?? '',
                ];
                
                fputcsv($stream, $row, ',', '"');
            }
            
            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-store, no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Genera un resumen de los resultados de importación
     */
    public function generateImportSummary(array $results): array
    {
        $summary = [
            'total' => count($results),
            'exitosos' => 0,
            'errores' => 0,
            'porcentaje_exito' => 0,
        ];

        foreach ($results as $result) {
            if ($result['status'] === 'success') {
                $summary['exitosos']++;
            } else {
                $summary['errores']++;
            }
        }

        if ($summary['total'] > 0) {
            $summary['porcentaje_exito'] = round(($summary['exitosos'] / $summary['total']) * 100, 2);
        }

        return $summary;
    }
}
