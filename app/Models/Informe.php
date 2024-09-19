<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "inf_id",
        "inf_path",
        "inf_fecha",
        
    ];
}
