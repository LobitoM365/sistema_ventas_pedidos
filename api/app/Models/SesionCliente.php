<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesionCliente extends Model
{
    use HasFactory;

    protected $table = 'sesiones_clientes';
    public $timestamps = false;

    protected $fillable = [
        'clientes_id',
        'token',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }
}
