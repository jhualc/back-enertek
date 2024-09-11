<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "emp_id",
        "emp_nombre",
        "emp_identificacion",
        "emp_telefono",
        "emp_direccion"
    ];
}
