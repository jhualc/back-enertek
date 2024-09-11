<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtrFacturacion extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "otf_id",
        "fac_id",
        "otr_id",
        "otf_valor",
        
    ];
}
