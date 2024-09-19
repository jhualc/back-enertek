<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Persona extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "per_id",
        "per_nombre",
        "per_correo",
        "per_cargo",
        "per_empresa",
        "per_tipo_persona",
        "per_bio",
        "per_foto",
    ];



}