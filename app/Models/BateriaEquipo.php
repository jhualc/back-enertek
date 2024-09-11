<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BateriaEquipo extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "beq_id",
        "equ_id",
        "bat_id",
        "beq_fecha"
    ];
}
