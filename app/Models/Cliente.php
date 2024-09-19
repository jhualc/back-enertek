<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'cliente'; 

    protected $primaryKey = 'cli_id'; 

    protected $fillable = [
        'cli_id',
        'cli_nombre',
        'cli_identificacion',
        'cli_tipo_identificacion',
        'otr_id', 
    ];
}