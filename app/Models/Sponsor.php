<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    use HasFactory, SoftDeletes; // Added SoftDeletes trait

    protected $table = 'sponsor'; // Explicitly set table name
    protected $primaryKey = 'spo_id'; // Explicitly set primary key

    protected $fillable = [
        "spo_logo",
        "spo_empresa",
        "spo_tipo",
        "spo_web",
        "spo_contacto",
        "spo_telefono",
        "spo_correo",
    ];

    protected $dates = ['deleted_at']; // Added for SoftDeletes
}
