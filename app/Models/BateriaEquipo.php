<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BateriaEquipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bateria_equipo'; 

    protected $primaryKey = 'beq_id'; 

    protected $fillable = [
        'beq_id',
        'equ_id',
        'bat_id',
        'beq_fecha',
        "created_at" ,
        "updated_at" ,
    ];

    protected $dates = ['deleted_at']; 
    
}
