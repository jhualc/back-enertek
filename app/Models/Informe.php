<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "inf_id",
        "inf_path",
        "inf_fecha",
        "created_at" ,
        "updated_at" ,
        
    ];

    protected $dates = ['deleted_at']; 
}
