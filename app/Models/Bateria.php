<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bateria extends Model
{
    use HasFactory;

    protected $table = 'bateria'; 

    protected $primaryKey = 'bat_id'; 

    protected $fillable = [
        'bat_id',
        'bat_modelo',
        'bat_voltaje',
        'bat_capacidad',
        'mar_id',
    ];
}
