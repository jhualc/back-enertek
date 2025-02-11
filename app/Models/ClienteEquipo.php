<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteEquipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cliente_equipo'; 

    protected $primaryKey = 'ceq_id'; 

    protected $fillable = [
        'cli_id',
        'equ_id',
        "created_at" ,
        "updated_at" ,
    ];
    protected $dates = ['deleted_at']; 

    public function cliente()
    {
        return $this->belongsTo(ClienteEquipo::class, 'ceq_id');
    }
}
