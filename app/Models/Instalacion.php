<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instalacion extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "ins_id",
        "ins_calibre_cable",
        "ins_cant_cable",
        "ins_breaker",
        "ins_otro",
        "ins_tablero",
        "equ_id",
        "created_at" ,
        "updated_at" ,
    ];

    protected $dates = ['deleted_at']; 
}
