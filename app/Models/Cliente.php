<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "cli_id",
        "cli_nombre",
        "cli_identificacion",
        "cli_tipo_identificacion",
        "otr_id"
    ];
}
