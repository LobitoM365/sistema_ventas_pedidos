<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoProducto;
use Illuminate\Http\Request;
use App\Repositories\PedidoRepository;
use App\Models\Producto;
use App\Repositories\VentaRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

/**
 * @OA\Info(
 *     title="API de Pedidos",
 *     version="1.0.0",
 *     description="API para gestionar pedidos y ventas, incluyendo la creación de pedidos, consulta de productos y entrega.",
 *     @OA\Contact(
 *         email="soporte@tusistema.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Utiliza un token de autorización Bearer para autenticar las solicitudes."
 * )
 * 
 * @OA\Schema(
 *     schema="Pedido",
 *     type="object",
 *     required={"id", "estado", "clientes_id", "administradores_id", "cobrado", "direccion", "ventas_id"},
 *     @OA\Property(property="id", type="integer", description="ID del pedido"),
 *     @OA\Property(property="estado", type="string", enum={"PENDIENTE", "ENTREGADO"}, description="Estado del pedido"),
 *     @OA\Property(property="fecha_creacion", type="string", format="date-time", description="Fecha de creación del pedido"),
 *     @OA\Property(property="fecha_actualizacion", type="string", format="date-time", description="Fecha de actualización del pedido"),
 *     @OA\Property(property="clientes_id", type="integer", description="ID del cliente"),
 *     @OA\Property(property="administradores_id", type="integer", description="ID del administrador"),
 *     @OA\Property(property="cobrado", type="integer", description="Monto cobrado"),
 *     @OA\Property(property="direccion", type="string", description="Dirección de entrega"),
 *     @OA\Property(property="fecha_entrega", type="string", format="date-time", description="Fecha de entrega"),
 *     @OA\Property(property="ventas_id", type="integer", description="ID de la venta asociada al pedido"),
 *     @OA\Property(
 *         property="productos",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Producto")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Producto",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID del producto"),
 *     @OA\Property(property="cantidad", type="integer", description="Cantidad del producto")
 * )
 */
class PedidoController extends Controller
{
    protected $pedidoRepo;
    protected $ventaRepo;

    public function __construct(PedidoRepository $pedidoRepo, VentaRepository $ventaRepo)
    {
        $this->pedidoRepo = $pedidoRepo;
        $this->ventaRepo = $ventaRepo;
    }

    /**
     * @OA\Post(
     *     path="/api/pedidos/crear",
     *     summary="Crear un nuevo pedido",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Pedido")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pedido creado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Pedido")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos inválidos"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $errors = [];

        // Validación de datos del pedido
        try {
            $request->validate([
                'clientes_id' => 'required|exists:clientes,id',
                'cobrado' => 'required|integer',
                'direccion' => 'required|string|max:255',
                'fecha_entrega' => 'nullable|date',
                'productos' => 'required|array', 
                'productos.*.cantidad' => 'required|numeric|min:1'
            ]);
        } catch (ValidationException $e) {
            $errors = array_merge($errors, $e->errors());
        }

        // Validación de productos
        if ($request->has('productos') && is_array($request->productos)) {
            $seenProducts = [];

            foreach ($request->productos as $index => $productoData) {
                if (!isset($productoData['id']) || !isset($productoData['cantidad'])) {
                    $errors['producto_' . ($index + 1)] = 'Faltan datos (ID o cantidad) en el producto.';
                    continue;
                }

                if (in_array($productoData['id'], $seenProducts)) {
                    $errors['producto_' . ($productoData['id'])] = 'El producto con ID ' . $productoData['id'] . ' está duplicado.';
                    continue;
                }

                $producto = Producto::find($productoData['id']);
                if (!$producto) {
                    $errors['producto_' . ($productoData['id'])] = 'No se encuentra registrado.';
                    continue;
                }

                if ($producto->stock < $productoData['cantidad']) {
                    $errors['producto_' . ($productoData['id'])] = 'No hay suficiente stock disponible. Stock actual: ' . ($producto->stock);
                }

                $seenProducts[] = $productoData['id'];
            }
        } else {
            $errors['productos'] = 'Se debe añadir al menos un producto al pedido.';
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $errors
            ], 422);
        }

        try {
            $pedido = $this->pedidoRepo->createPedido($request->all());
            return response()->json($pedido, 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al crear el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/pedidos/listar",
     *     summary="Obtener todos los pedidos",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pedidos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Pedido")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $pedidos = Pedido::all();
        return response()->json($pedidos);
    }

    /**
     * @OA\Get(
     *     path="/api/pedidos/buscar/{id}",
     *     summary="Obtener un pedido por su ID",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del pedido",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Pedido")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pedido no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }

    /**
     * @OA\Put(
     *     path="/api/pedidos/entregar/{id}",
     *     summary="Marcar un pedido como entregado",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del pedido",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="string", description="Usuario que realiza la entrega")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido entregado correctamente",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="Se entregó el pedido y se generó la venta exitosamente."))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Pedido no encontrado o no en estado PENDIENTE"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al generar la venta"
     *     )
     * )
     */
    public function deliver($id, Request $request)
    {
        try {
            $pedido = Pedido::find($id);

            if (!$pedido) {
                return response()->json(['message' => 'El pedido no se encuentra registrado.'], 400);
            }

            if ($pedido->estado !== 'PENDIENTE') {
                return response()->json(['message' => 'El pedido no puede ser entregado porque no está en estado PENDIENTE.'], 400);
            }

            $pedido = $this->pedidoRepo->deliverPedido($id);
            if ($pedido) {
                $productos = PedidoProducto::where('pedidos_id', $pedido->id)
                    ->get(['productos_id as id', 'cantidad']);

                $array_venta = array_merge($pedido->toArray(), ["productos" => $productos], ["user" => $request->user]);

                $venta = $this->ventaRepo->createVentaPedido($array_venta);

                if ($venta) {
                    $pedido->ventas_id = $venta->id;
                    $pedido->save();
                    return response()->json(["message" => "Se entregó el pedido y se generó la venta exitosamente."], 200);
                } else {
                    return response()->json(["message" => "Error al generar la venta."], 500);
                }
            } else {
                return response()->json(["message" => "Error al entregar el pedido."], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al entregar el pedido', 'error' => $e->getMessage()], 500);
        }
    }
}
