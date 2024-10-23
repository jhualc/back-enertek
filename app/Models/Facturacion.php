<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facturacion extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "fac_id",
        "fac_fecha",
        "fac_total",
        "fac_estado",
        "created_at" ,
        "updated_at" ,
    ];

    protected $dates = ['deleted_at']; 
}
