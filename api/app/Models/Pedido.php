<?php
// app/Models/Pedido.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'estado',
        'clientes_id',
        'administradores_id',
        'cobrado',
        'direccion',
        'fecha_entrega',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_productos')->withPivot('cantidad', 'costo');
    }
}
