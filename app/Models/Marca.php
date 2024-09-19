<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marca'; 

    protected $fillable = [
        'mar_id',
        'mar_descripcion',
    ];

    protected $primaryKey = 'mar_id'; 
}