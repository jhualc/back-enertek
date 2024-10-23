<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipo'; 
    
    protected $fillable = [
    
        "equ_id",
        "equ_modelo",
        "equ_serial",
        "mar_id",
        "teq_id",
        "equ_cant_baterias",
        "ins_id",
        "created_at" ,
        "updated_at" ,
    ];
    protected $primaryKey = 'equ_id';
    protected $dates = ['deleted_at']; 
}
