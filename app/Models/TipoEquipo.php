<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    use HasFactory;

    protected $table = 'tipo_equipo'; 
    protected $fillable = [
        'teq_id',
        'teq_descripcion',
    ];

    protected $primaryKey = 'teq_id'; 
}
