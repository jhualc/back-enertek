<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instalacion extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "ins_id",
        "ins_calibre_cable",
        "ins_cant_cable",
        "ins_breaker",
        "ins_otro",
        "ins_tablero"
    ];
}
