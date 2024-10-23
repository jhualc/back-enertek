<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtePersonal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ote-personal'; 
    protected $fillable = [
    
        "otp_id",
        "per_id",
        "ote_id",
        "created_at" ,
        "updated_at" ,
    ];

    protected $dates = ['deleted_at']; 
}
