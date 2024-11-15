<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteSede extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cliente_sedes'; 

    protected $primaryKey = 'cls_id'; 

    protected $fillable = [
        'cls_id',
        'cls_descripcion',
        'cls_direccion',
        'cli_id',
        "created_at" ,
        "updated_at" ,
    ];
    protected $dates = ['deleted_at']; 

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cli_id');
    }
}
