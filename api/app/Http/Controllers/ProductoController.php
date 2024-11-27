<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use App\Repositories\ProductoRepository;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    protected $productoRepo;

    public function __construct(ProductoRepository $productoRepo)
    {
        $this->productoRepo = $productoRepo;
    }

    // Crear un nuevo producto
    public function store(Request $request)
    {
        try {
            // Validación de los datos
            $request->validate([
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:1200',
                'precio_venta' => 'required|numeric',
                'precio_base' => 'required|numeric',
                'medida' => 'required|in:unidades,metros,kilos,litros',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Crear producto usando el repositorio
            $producto = $this->productoRepo->createProducto($request->all());

            if (!$producto) {
                return response()->json(['message' => 'Error al crear el producto'], 500);
            }

            return response()->json($producto, 201);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error en la base de datos', 'error' => $e->getMessage()], 500);
        }
    }

    // Listar todos los productos
    public function index()
    {
        try {
            // Obtener todos los productos
            $productos = $this->productoRepo->getAllProductos();

            if (!$productos) {
                return response()->json(['message' => 'No se encontraron productos'], 404);
            }

            return response()->json($productos);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al obtener productos', 'error' => $e->getMessage()], 500);
        }
    }

    // Buscar un producto por ID
    public function show($id)
    {
        try {
            $producto = $this->productoRepo->getProductoById($id);

            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            return response()->json($producto);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al obtener el producto', 'error' => $e->getMessage()], 500);
        }
    }

    // Actualizar un producto
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string|max:1200',
                'precio_venta' => 'required|numeric',
                'precio_base' => 'required|numeric',
                'medida' => 'required|in:unidades,metros,kilos,litros',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $producto = $this->productoRepo->updateProducto($id, $request->all());

            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado o error al actualizar'], 404);
            }

            return response()->json($producto);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al actualizar el producto', 'error' => $e->getMessage()], 500);
        }
    }

    // Eliminar un producto
    public function destroy($id)
    {
        try {
            $producto = $this->productoRepo->deleteProducto($id);

            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado o error al eliminar'], 404);
            }

            return response()->json(['message' => 'Producto eliminado'], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al eliminar el producto', 'error' => $e->getMessage()], 500);
        }
    }

    // Añadir stock a un producto
    public function addStock(Request $request, $id)
    {
        try {
            // Validación de los datos de stock
            $request->validate([
                'cantidad' => 'required|numeric|min:1'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $producto = $this->productoRepo->getProductoById($id);

            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            // Añadir stock al producto
            $producto->stock += $request->cantidad;
            $producto->save();

            return response()->json([
                'message' => 'Stock añadido exitosamente',
                'producto' => $producto
            ], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al añadir el stock', 'error' => $e->getMessage()], 500);
        }
    }

    // Quitar stock a un producto
    public function removeStock(Request $request, $id)
    {
        try {
            // Validación de los datos de stock
            $request->validate([
                'cantidad' => 'required|numeric|min:1'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $producto = $this->productoRepo->getProductoById($id);

            if (!$producto) {
                return response()->json(['message' => 'Producto no encontrado'], 404);
            }

            // Verificar que el stock no sea menor que la cantidad a quitar
            if ($producto->stock < $request->cantidad) {
                return response()->json([
                    'message' => 'No hay suficiente stock para quitar',
                    'stock_actual' => $producto->stock
                ], 400);
            }

            // Quitar stock del producto
            $producto->stock -= $request->cantidad;
            $producto->save();

            return response()->json([
                'message' => 'Stock quitado exitosamente',
                'producto' => $producto
            ], 200);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al quitar el stock', 'error' => $e->getMessage()], 500);
        }
    }
}
