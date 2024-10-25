<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "spo_id",
        "spo_logo",
        "spo_empresa",
        "spo_tipo",
        "spo_web",
        "spo_contacto",
        "spo_telefono",
        "spo_correo",
        "created_at" ,
        "updated_at" ,
    ];
}
