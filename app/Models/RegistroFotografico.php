<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroFotografico extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "ref_id",
        "ref_ubicacion",
        "ins_id",
        "ote_id",
        "ref_fecha"
    ];
}