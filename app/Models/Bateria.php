<?php

namespace App\Models;

use App\Models\Marca;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bateria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bateria'; 

    protected $primaryKey = 'bat_id'; 

    protected $fillable = [
        'bat_modelo',
        'bat_voltaje',
        'bat_capacidad',
        'mar_id',
    ];

    protected $dates = ['deleted_at'];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'mar_id', 'mar_id');
    }
}
