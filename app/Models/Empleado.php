<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "emp_id",
        "emp_nombre",
        "emp_identificacion",
        "emp_telefono",
        "emp_direccion",
        "created_at" ,
        "updated_at" ,
    ];
    protected $dates = ['deleted_at']; 
}
