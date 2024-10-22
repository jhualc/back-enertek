<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class EventoPersona extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "evp_id",
        "eve_id",
        "per_id",
        "eve_descripcion",
        "eve_hora",
        "eve_tipo",
        "eve_dia",
        "eve_orden",
        "eve_resumen",
        "per_nombre",
        "per_correo",
        "per_empresa",
        "per_tipo_persona",
        "per_bio",
        "per_foto",
        "created_at" ,
        "updated_at" ,

    ];

    protected $dates = ['deleted_at']; 



}