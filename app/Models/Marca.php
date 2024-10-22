<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'marca'; 

    protected $fillable = [
        'mar_id',
        'mar_descripcion',
        "created_at" ,
        "updated_at" ,
        "created_at" ,
        "updated_at" ,
    ];

    protected $primaryKey = 'mar_id'; 
    protected $dates = ['deleted_at']; 
}