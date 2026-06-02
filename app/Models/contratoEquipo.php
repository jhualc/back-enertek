<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class contratoEquipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contrato_equipo'; 

    protected $primaryKey = 'coe_id'; // Explicitly set primary key
    protected $fillable = [
        "equ_id",
        "con_id",
        "coe_periodicidad",
    ];

    protected $dates = ['deleted_at']; 
}
