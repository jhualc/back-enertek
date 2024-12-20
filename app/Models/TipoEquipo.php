<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoEquipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_equipo'; 
    protected $fillable = [
        'teq_id',
        'teq_descripcion',
        "created_at" ,
        "updated_at" ,
    ];

    protected $primaryKey = 'teq_id'; 

    protected $dates = ['deleted_at']; 
 
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'teq_id', 'teq_id');
    }
}
