<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "teq_id",
        "teq_descripcion"
        
    ];
}
