<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Agenda extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "eve_id",
        "eve_descripcion",
        "eve_hora",
        "eve_tipo",
        "eve_dia",
        "eve_orden",
        "created_at" ,
        "updated_at" ,
    ];

    protected $dates = ['deleted_at']; 

}