<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    // Especificar la tabla de la base de datos
    protected $table = 'ventas';
    
    public $timestamps = false;

    // Campos que son asignables en masa
    protected $fillable = [
        'clientes_id',
        'administradores_id',
        'estado',
    ];

    // Relación con el modelo Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

    // Relación con el modelo Administrador
    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'administradores_id');
    }

    // Relación con los productos de la venta
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'ventas_productos', 'ventas_id', 'productos_id')
            ->withPivot('cantidad', 'costo');
    }
}
