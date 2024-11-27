<?php

namespace App\Repositories;

use App\Models\Producto;

class ProductoRepository
{
    // Crear un producto
    public function createProducto(array $data)
    {
        return Producto::create($data);
    }

    // Obtener todos los productos
    public function getAllProductos()
    {
        return Producto::all();
    }

    // Obtener un producto por ID
    public function getProductoById($id)
    {
        return Producto::find($id);
    }

    // Actualizar un producto
    public function updateProducto($id, array $data)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->update($data);
            return $producto;
        }
        return null;
    }

    // Eliminar un producto
    public function deleteProducto($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->delete();
            return true;
        }
        return false;
    }
}
