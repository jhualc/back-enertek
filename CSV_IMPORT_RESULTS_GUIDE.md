# CSV Result Reporting - Guía de Uso

## Descripción

Se ha implementado la generación automática de archivos CSV con los resultados de la importación masiva. Cuando se procesa una carga de Excel, el sistema ahora devuelve un archivo CSV con el estado de cada registro (éxito o error) y la razón del error si aplica.

## Endpoints Disponibles

### 1. Procesar Batch y Obtener CSV

**URL:** `POST /api/import-process-batch`

**Parámetros:**
```json
{
  "batch_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Respuesta:** Archivo CSV (descarga automática)

**Para obtener JSON en lugar de CSV:**
```json
{
  "batch_id": "550e8400-e29b-41d4-a716-446655440000",
  "format": "json"
}
```

---

### 2. Descargar CSV de Un Batch Ya Procesado

**URL:** `GET /api/import-results-csv?batch_id=550e8400-e29b-41d4-a716-446655440000`

**Respuesta:** Archivo CSV (descarga automática)

---

### 3. Migrar Clientes y Obtener CSV

**URL:** `POST /api/import-migrate-clients`

**Parámetros:**
```json
{
  "batch_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Respuesta:** Archivo CSV (descarga automática)

---

## Estructura del CSV

El archivo CSV contiene las siguientes columnas:

| Columna | Descripción | Ejemplo |
|---------|-------------|---------|
| Fila | Número de fila del Excel original | 2 |
| Estado | Exitoso o Error | Exitoso |
| Razón del Error | Descripción del error (si aplica) | Identificación de cliente requerida |
| Identificación | Número de identificación del cliente | 1234567890 |
| Nombre | Nombre de la empresa/persona | Empresa XYZ |
| Tipo Identificación | Tipo de ID (Cédula, RUC, NIT) | NIT |
| Sede | Nombre de la sede | Bogotá |
| Marca Equipo | Marca del equipo UPS | APC |
| Modelo Equipo | Modelo del equipo | Smart-UPS 2000 |
| Serial Equipo | Serial del equipo | A1B2C3D4 |

---

## Ejemplo de Uso Completo

### Paso 1: Cargar Excel

```bash
curl -X POST http://localhost:8000/api/upload-cliente-full \
  -F "file=@clientes.xlsx"

# Respuesta:
# {
#   "message": "Excel cargado a staging exitosamente con 100 registros",
#   "batchId": "550e8400-e29b-41d4-a716-446655440000",
#   "processedCount": 100
# }
```

### Paso 2: Procesar Batch y Obtener CSV

```bash
curl -X POST http://localhost:8000/api/import-process-batch \
  -H "Content-Type: application/json" \
  -d '{"batch_id": "550e8400-e29b-41d4-a716-446655440000"}' \
  -o resultados_importacion.csv

# El archivo resultados_importacion.csv se descargará automáticamente
```

### Paso 3: Revisar Resultados

Abra el archivo CSV en Excel:
- Fila 1: Encabezados
- Filas 2-101: Resultados de cada registro
- Busque registros con "Error" en la columna "Estado" para revisar problemas

---

## Respuesta JSON (Alternativa)

Si prefiere obtener JSON en lugar de CSV:

```bash
curl -X POST http://localhost:8000/api/import-process-batch \
  -H "Content-Type: application/json" \
  -d '{"batch_id": "550e8400-e29b-41d4-a716-446655440000", "format": "json"}'

# Respuesta:
{
  "message": "Batch 550e8400... procesado",
  "summary": {
    "total": 100,
    "exitosos": 98,
    "errores": 2,
    "porcentaje_exito": 98.0
  },
  "results": [
    {
      "row": 2,
      "status": "success",
      "error": null,
      "data": {...}
    },
    {
      "row": 3,
      "status": "error",
      "error": "Identificación de cliente requerida",
      "data": {...}
    }
  ]
}
```

---

## Mensajes de Error Comunes

Algunos de los mensajes de error que puede encontrar en el CSV:

| Error | Significado |
|-------|------------|
| "Identificación de cliente requerida" | Falta el número de identificación |
| "Dirección de sede requerida" | Falta la dirección de la sede |
| "Email de contacto requerido" | Falta el email del contacto |
| "Marca de batería requerida" | Falta la marca de la batería |
| Otros errores de base de datos | Violación de restricciones o validaciones |

---

## Notas Importantes

1. **Formato CSV:** El archivo se genera en UTF-8 con BOM (compatible con Excel)
2. **Descargas:** Los archivos se descargan automáticamente con timestamp en el nombre
3. **Errores:** Cada registro se procesa independientemente; un error no detiene el procesamiento
4. **Reintentos:** Puede procesar el mismo `batch_id` múltiples veces si es necesario
5. **Almacenamiento:** Los resultados se guardan en la base de datos; puede descargar el CSV nuevamente usando el endpoint `/api/import-results-csv`

---

## Características de la Implementación

✅ Generación automática de CSV después de procesar  
✅ Incluye datos de éxito y error en un solo archivo  
✅ Compatible con Excel (UTF-8 + BOM)  
✅ Resumen de estadísticas (total, exitosos, errores, porcentaje)  
✅ Opción de obtener JSON en lugar de CSV  
✅ Endpoint dedicado para descargar resultados posteriores  
✅ Descripciones de error detalladas para cada registro fallido  

---

## Archivos Modificados

- `app/Traits/ImportResultsTrait.php` (NUEVO)
- `app/Http/Controllers/ExcelImportProcessorController.php`
- `app/Http/Controllers/StagingToClientController.php`

---

## Próximos Pasos Recomendados

1. Actualizar `routes/api.php` para incluir el nuevo endpoint:
   ```php
   Route::get('/import-results-csv', [ExcelImportProcessorController::class, 'getImportResultsCsv']);
   ```

2. Probar con un archivo Excel pequeño (5-10 registros) para validar el flujo

3. Implementar lógica de reintento para registros con error (si es necesario)

