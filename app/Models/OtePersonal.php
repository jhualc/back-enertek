<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtePersonal extends Model
{
    use SoftDeletes;
    protected $fillable = [
    
        "otp_id",
        "per_id",
        "ote_id",
        
    ];
}
