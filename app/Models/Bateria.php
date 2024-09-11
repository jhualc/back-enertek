<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bateria extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "bat_id",
        "bat_modelo",
        "bat_voltaje",
        "bat_capacidad",
        "mar_id"
    ];

}
