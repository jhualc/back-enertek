<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrato extends Model
{
    use HasFactory;

    protected $table = 'contrato'; 

    protected $primaryKey = 'con_id'; 

    protected $fillable = [
        'con_id',
        'con_tipo',
        'con_valor',
        'con_periodicidad',
        'con_estado',
        'cli_id', 
    ];
}
