<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "otr_id",
        "otr_fecha_creacion",
        "otr_descripcion",
        "otr_valor_cotizado",
        "otr_estado",
        "created_at" ,
        "updated_at" ,
    ];
    protected $dates = ['deleted_at']; 
}