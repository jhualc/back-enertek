<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Agenda extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        "eve_descripcion",
        "eve_hora",
        "eve_tipo",
        "eve_dia",
        "eve_orden",
    ];
    
    protected $primaryKey = 'eve_id';

    protected $dates = ['deleted_at']; 

}