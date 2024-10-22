<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contratoEquipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contrato_equipo'; 
    protected $fillable = [
    
        "coe_id",
        "equi_id",
        "con_id",
        "coe_periodicidad",
        "cli_id",
        "created_at" ,
        "updated_at" ,
    ];

    protected $dates = ['deleted_at']; 
}
