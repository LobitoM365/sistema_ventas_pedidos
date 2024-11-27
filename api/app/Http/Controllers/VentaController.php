<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\VentaRepository;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class VentaController extends Controller
{
    protected $ventaRepo;

    // Constructor para inyectar el repositorio
    public function __construct(VentaRepository $ventaRepo)
    {
        $this->ventaRepo = $ventaRepo;
    }

    // Crear una nueva venta
    public function store(Request $request)
    {
        $errors = [];

        // Validamos los datos de la venta (cliente, administrador, productos, etc.)
        try {
            $request->validate([
                'clientes_id' => 'required|exists:clientes,id',
                'productos' => 'required|array',  // Aseguramos que productos sea un array
                'productos.*.id' => 'required|exists:productos,id',  // Verificamos que cada producto exista
                'productos.*.cantidad' => 'required|numeric|min:1',  // Verificamos que la cantidad sea válida
            ]);
        } catch (ValidationException $e) {
            // Si hay errores de validación, los agregamos al array de errores
            $errors = array_merge($errors, $e->errors());
        }

        // Verificamos que los productos sean válidos
        if ($request->has('productos') && is_array($request->productos)) {
            $seenProducts = []; // Para hacer el seguimiento de los productos ya procesados

            // Verificamos si los productos existen en la base de datos
            foreach ($request->productos as $index => $productoData) {
                // Validamos que cada producto tenga un ID y cantidad
                if (!isset($productoData['id']) || !isset($productoData['cantidad'])) {
                    $errors['producto_' . ($index + 1)] = 'Faltan datos (ID o cantidad) en el producto.';
                    continue; // Si faltan datos, no lo procesamos más
                }

                // Verificamos si el producto ya fue procesado (para evitar duplicados)
                if (in_array($productoData['id'], $seenProducts)) {
                    $errors['producto_' . ($productoData['id'])] = 'El producto con ID ' . $productoData['id'] . ' está duplicado.';
                    continue;
                }

                // Buscamos el producto en la base de datos
                $producto = Producto::find($productoData['id']);

                // Si no existe el producto, lo agregamos al array de errores
                if (!$producto) {
                    $errors['producto_' . ($productoData['id'])] = 'No se encuentra registrado.';
                    continue;
                }

                // Validación de stock: si la cantidad solicitada es mayor al stock disponible
                if ($producto->stock < $productoData['cantidad']) {
                    $errors['producto_' . ($productoData['id'])] = 'No hay suficiente stock disponible. Stock actual: ' . ($producto->stock);
                }

                // Marcamos el producto como procesado
                $seenProducts[] = $productoData['id'];
            }
        } else {
            // Si no se envían productos o no es un array, agregamos un error
            $errors['productos'] = 'Se debe añadir al menos un producto a la venta.';
        }

        // Si hay errores, retornamos todos los errores juntos
        if (!empty($errors)) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $errors
            ], 422);
        }

        try {
            // Crear la venta usando el repositorio
            $venta = $this->ventaRepo->createVenta($request->all());

            return response()->json($venta, 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al crear la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener todas las ventas
    public function index()
    {
        $ventas = Venta::all();
        return response()->json($ventas);
    }

    // Obtener una venta por su ID
    public function show($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        return response()->json($venta);
    }
}
