<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "mar_id",
        "mar_descripcion"
        
    }
    
];