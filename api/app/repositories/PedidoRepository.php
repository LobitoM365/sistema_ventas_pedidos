<?php

// app/Repositories/PedidoRepository.php
namespace App\Repositories;

use App\Models\Pedido;
use App\Models\PedidoProducto;
use App\Models\Producto;
use Illuminate\Database\QueryException;

class PedidoRepository
{
    // Crear un nuevo pedido
    public function createPedido($data)
    {
        try {
            // Crear el pedido
            $pedido = Pedido::create([
                'clientes_id' => $data['clientes_id'],
                'administradores_id' => $data["user"]["id"],
                'cobrado' => $data['cobrado'],
                'direccion' => $data['direccion'],
            ]);

            // Agregar los productos al pedido
            foreach ($data['productos'] as $productoData) {
                $producto = Producto::find($productoData['id']);

                if ($producto) {
                    PedidoProducto::create([
                        'pedidos_id' => $pedido->id,
                        'productos_id' => $producto->id,
                        'cantidad' => $productoData['cantidad'],
                        'costo' => $producto->precio_venta
                    ]);

                    $producto->stock -= $productoData['cantidad'];
                    $producto->save();
                }
            }

            return $pedido;
        } catch (QueryException $e) {
            throw new QueryException("", 'Error al crear el pedido: ' . $e->getMessage(), [], $e);
        }
    }

    public function deliverPedido($id)
    {
        // Cambiamos el estado del pedido a ENTREGADO

        $pedido = Pedido::find($id);
        $pedido->estado = 'ENTREGADO';
        $pedido->fecha_entrega = now();
        $pedido->save();

        return $pedido;
    }
}
