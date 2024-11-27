<?php

namespace App\Repositories;

use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Database\QueryException;

class VentaRepository
{
    // Crear una nueva venta
    public function createVenta($data)
    {
        try {
            // Crear la venta
            $venta = Venta::create([
                'clientes_id' => $data['clientes_id'],
                'administradores_id' => $data['user']['id'],
                'estado' => 'ACTIVO',
            ]);

            // Agregar los productos a la venta
            foreach ($data['productos'] as $productoData) {
                $producto = Producto::find($productoData['id']);

                if ($producto) {
                    // Registrar el producto en la tabla intermedia ventas_productos
                    $venta->productos()->attach($producto->id, [
                        'cantidad' => $productoData['cantidad'],
                        'costo' => $producto->precio_venta
                    ]);

                    $producto->stock -= $productoData['cantidad'];
                    $producto->save();
                }
            }

            return $venta;
        } catch (QueryException $e) {
            throw new QueryException("", 'Error al crear la venta: ' . $e->getMessage(), [], $e);
        }
    }

    // Crear una nueva venta de un pedido
    public function createVentaPedido($data)
    {
        try {
            // Crear la venta
            $venta = Venta::create([
                'clientes_id' => $data['clientes_id'],
                'administradores_id' => $data['user']['id'],
                'estado' => 'ACTIVO',
            ]);

            // Agregar los productos a la venta
            foreach ($data['productos'] as $productoData) {
                $producto = Producto::find($productoData['id']);

                if ($producto) {
                    // Registrar el producto en la tabla intermedia ventas_productos
                    $venta->productos()->attach($producto->id, [
                        'cantidad' => $productoData['cantidad'],
                        'costo' => $producto->precio_venta
                    ]);
                }
            }

            return $venta;
        } catch (QueryException $e) {
            throw new QueryException("", 'Error al crear la venta: ' . $e->getMessage(), [], $e);
        }
    }
}
