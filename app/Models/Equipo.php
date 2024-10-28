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
        "equ_qr_code",
        "created_at" ,
        "updated_at" ,
    ];
    protected $primaryKey = 'equ_id';
    protected $dates = ['deleted_at']; 

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'mar_id', 'mar_id');
    }

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'teq_id', 'teq_id');
    }
}
