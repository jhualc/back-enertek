<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroFotografico extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
    
        "ref_id",
        "ref_ubicacion",
        "ins_id",
        "ote_id",
        "ref_fecha",
        "created_at" ,
        "updated_at" ,
    ];
    protected $dates = ['deleted_at']; 
}
