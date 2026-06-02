<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtePersonal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ote_personal'; 
    protected $fillable = [
        "per_id",
        "otr_id",
    ];

    protected $dates = ['deleted_at']; 
}
