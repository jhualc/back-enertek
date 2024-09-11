<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturacion extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "fac_id",
        "fac_fecha",
        "fac_total",
        "fac_estado"
    ];
}
