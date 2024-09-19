<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "con_id",
        "con_tipo",
        "con_valor",
        "con_periodicidad",
        "con_estado",
        "cli_id"
    ];
}
