<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;


class Agenda extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "eve_id",
        "eve_descripcion",
        "eve_hora",
        "eve_tipo",
        "eve_dia",
        "eve_orden"
    ];



}