<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoProducto extends Model
{
    use HasFactory;

    protected $fillable = [
        'estado',
        'pedidos_id',       // Agrega pedidos_id aquí
        'productos_id',
        'cantidad',
        'costo',
    ];
}
