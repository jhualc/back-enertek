<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contratoEquipo extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "coe_id",
        "equi_id",
        "con_id",
        "coe_periodicidad",
        "cli_id"
    ];
}
