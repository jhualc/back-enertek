<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\StreamedResponse;

trait ImportResultsTrait
{
    /**
     * Genera un archivo CSV con los resultados de importación.
     *
     * Estados soportados:
     * - success   = Exitoso
     * - duplicate = Duplicado
     * - error     = Error
     *
     * @param array $results
     * @param string $filename
     * @return StreamedResponse
     */
    public function generateImportResultsCsv(
        array $results,
        string $filename = 'resultados_importacion.csv'
    ): StreamedResponse {
        
        return response()->stream(function () use ($results) {

            $stream = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel reconozca correctamente tildes y caracteres especiales
            fprintf(
                $stream,
                chr(0xEF) . chr(0xBB) . chr(0xBF)
            );

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

            fputcsv(
                $stream,
                $headers,
                ',',
                '"'
            );

            // Escribir resultados
            foreach ($results as $result) {

                $data = $result['data'] ?? [];

                /*
                 * Traducir el estado interno a un estado
                 * entendible para el usuario.
                 */
                switch ($result['status'] ?? 'error') {

                    case 'success':
                        $status = 'Exitoso';
                        break;

                    case 'duplicate':
                        $status = 'Duplicado';
                        break;

                    case 'error':
                    default:
                        $status = 'Error';
                        break;
                }

                /*
                 * La razón solamente se muestra para
                 * duplicados y errores.
                 */
                $reason = '';

                if (
                    ($result['status'] ?? '') === 'duplicate' ||
                    ($result['status'] ?? '') === 'error'
                ) {
                    $reason = $result['error']
                        ?? 'Sin descripción';
                }

                $row = [
                    $result['row'] ?? '',
                    $status,
                    $reason,

                    $data['eis_identificacion']
                        ?? $data['cli_identificacion']
                        ?? '',

                    $data['eis_nombre_empresa_persona']
                        ?? $data['cli_nombre']
                        ?? '',

                    $data['eis_tipo_identificacion']
                        ?? $data['cli_tipo_identificacion']
                        ?? '',

                    $data['eis_sede']
                        ?? '',

                    $data['eis_marca_equipo']
                        ?? '',

                    $data['eis_modelo_equipo']
                        ?? '',

                    $data['eis_serial_equipo']
                        ?? '',
                ];

                fputcsv(
                    $stream,
                    $row,
                    ',',
                    '"'
                );
            }

            fclose($stream);

        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' =>
                'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-store, no-cache',
            'Expires' => '0',
        ]);
    }


    /**
     * Genera un resumen de los resultados de importación.
     *
     * Estados:
     * - success
     * - duplicate
     * - error
     */
    public function generateImportSummary(array $results): array
    {
        $summary = [
            'total' => count($results),
            'exitosos' => 0,
            'duplicados' => 0,
            'errores' => 0,
            'porcentaje_exito' => 0,
        ];

        foreach ($results as $result) {

            switch ($result['status'] ?? 'error') {

                case 'success':
                    $summary['exitosos']++;
                    break;

                case 'duplicate':
                    $summary['duplicados']++;
                    break;

                case 'error':
                default:
                    $summary['errores']++;
                    break;
            }
        }

        /*
         * El porcentaje de éxito se calcula únicamente
         * sobre los registros realmente creados.
         */
        if ($summary['total'] > 0) {
            $summary['porcentaje_exito'] = round(
                ($summary['exitosos'] / $summary['total']) * 100,
                2
            );
        }

        return $summary;
    }
}
