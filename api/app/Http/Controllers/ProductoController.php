<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use App\Repositories\ProductoRepository;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;


/**
 * @OA\Schema(
 *     schema="Producto",
 *     type="object",
 *     required={"id", "estado", "fecha_creacion", "nombre", "precio_venta", "precio_base", "medida"},
 *     @OA\Property(property="id", type="integer", description="ID del producto", example=101),
 *     @OA\Property(property="estado", type="string", enum={"ACTIVO", "INACTIVO"}, description="Estado del producto", example="ACTIVO"),
 *     @OA\Property(property="fecha_creacion", type="string", format="date-time", description="Fecha de creación del producto", example="2024-11-27T10:00:00Z"),
 *     @OA\Property(property="fecha_actualizacion", type="string", format="date-time", description="Fecha de actualización del producto", example="2024-11-27T12:00:00Z"),
 *     @OA\Property(property="nombre", type="string", description="Nombre del producto", example="Producto A"),
 *     @OA\Property(property="descripcion", type="string", description="Descripción del producto", example="Este es un producto de ejemplo."),
 *     @OA\Property(property="precio_venta", type="number", format="float", description="Precio de venta del producto", example=20.50),
 *     @OA\Property(property="precio_base", type="number", format="float", description="Precio base del producto", example=15.00),
 *     @OA\Property(property="medida", type="string", enum={"unidades", "metros", "kilos", "litros"}, description="Unidad de medida del producto", example="kilos")
 * )
 */
class ProductoController extends Controller
{
    protected $productoRepo;

    public function __construct(ProductoRepository $productoRepo)
    {
        $this->productoRepo = $productoRepo;
    }

    /**
     * @OA\Post(
     *     path="api/administradores/productos/registrar",
     *     summary="Crear un nuevo producto",
     *     tags={"Productos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "precio_venta", "precio_base", "medida"},
     *             @OA\Property(property="nombre", type="string", description="Nombre del producto", example="Producto A"),
     *             @OA\Property(property="descripcion", type="string", description="Descripción del producto", example="Descripción detallada del Producto A"),
     *             @OA\Property(property="precio_venta", type="number", format="float", description="Precio de venta", example=100.50),
     *             @OA\Property(property="precio_base", type="number", format="float", description="Precio base", example=80.00),
     *             @OA\Property(property="medida", type="string", description="Unidad de medida", enum={"unidades", "metros", "kilos", "litros"}, example="unidades")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Producto")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error en la base de datos"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="api/administradores/productos/listar",
     *     summary="Listar todos los productos",
     *     tags={"Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Producto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener productos"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="api/administradores/productos/buscar/{id}",
     *     summary="Buscar un producto por ID",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto a obtener",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del producto",
     *         @OA\JsonContent(ref="#/components/schemas/Producto")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener el producto"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="api/administradores/productos/actualizar/{id}",
     *     summary="Actualizar un producto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "precio_venta", "precio_base", "medida"},
     *             @OA\Property(property="nombre", type="string", description="Nombre del producto", example="Producto A"),
     *             @OA\Property(property="descripcion", type="string", description="Descripción del producto", example="Descripción detallada del Producto A"),
     *             @OA\Property(property="precio_venta", type="number", format="float", description="Precio de venta", example=100.50),
     *             @OA\Property(property="precio_base", type="number", format="float", description="Precio base", example=80.00),
     *             @OA\Property(property="medida", type="string", description="Unidad de medida", enum={"unidades", "metros", "kilos", "litros"}, example="unidades")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Producto")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar el producto"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="api/administradores/productos/eliminar/{id}",
     *     summary="Eliminar un producto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar el producto"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="api/administradores/productos/add/stock/{id}",
     *     summary="Añadir stock a un producto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto al que se le añadirá stock",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cantidad"},
     *             @OA\Property(property="cantidad", type="number", format="float", description="Cantidad de stock a añadir", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock añadido exitosamente",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Producto"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al añadir el stock"
     *     )
     * )
     */
    public function addStock(Request $request, $id)
    {
        try {
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

    /**
     * @OA\Post(
     *     path="api/administradores/productos/remove/stock/{id}",
     *     summary="Quitar stock a un producto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto del que se le quitará stock",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cantidad"},
     *             @OA\Property(property="cantidad", type="number", format="float", description="Cantidad de stock a quitar", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock quitado exitosamente",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Producto"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al quitar el stock"
     *     )
     * )
     */
    public function removeStock(Request $request, $id)
    {
        try {
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
