<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistroFotografico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registro_fotografico';
    protected $primaryKey = 'ref_id';

    protected $fillable = [
        "ref_ubicacion",
        "ins_id",
        "otr_id",
        "ref_fecha",
    ];
    protected $dates = ['deleted_at']; 
}
