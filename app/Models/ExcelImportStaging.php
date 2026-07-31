<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExcelImportStaging extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'excel_import_staging';

    protected $primaryKey = 'id';

    protected $fillable = [
        'eis_sector_empresa',
        'eis_tipo_cliente',
        'eis_sigla',
        'eis_tipo_equipo',
        'eis_nombre_empresa_persona',
        'eis_tipo_identificacion',
        'eis_identificacion',
        'eis_dv',
        'eis_departamento',
        'eis_ciudad',
        'eis_direccion',
        'eis_sede',
        'eis_ubicacion_equipo',
        'eis_nombre_contacto_1',
        'eis_correo_contacto_1',
        'eis_telefono_contacto_1',
        'eis_nombre_contacto_2',
        'eis_correo_contacto_2',
        'eis_telefono_contacto_2',
        'eis_estado_cliente',
        'eis_tipo_relacion_comercial',
        'eis_marca_equipo',
        'eis_modelo_equipo',
        'eis_potencia_kva',
        'eis_serial_equipo',
        'eis_cantidad_baterias_int',
        'eis_cantidad_baterias_ext',
        'eis_marca_bateria',
        'eis_referencia_bateria',
        'eis_voltaje_bateria',
        'eis_amperaje_bateria',
        'eis_snmps',
        'import_status',
        'import_error',
        'import_batch_id',
    ];

    protected $dates = ['deleted_at'];
}
