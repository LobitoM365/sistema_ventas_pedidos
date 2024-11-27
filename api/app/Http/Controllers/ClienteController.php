<?php

namespace App\Http\Controllers;

use App\Repositories\SesionClienteRepository;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    protected $sesionClienteRepository;
    protected $ClienteRepository;

    public function __construct(SesionClienteRepository $sesionClienteRepository, ClienteRepository $ClienteRepository)
    {
        $this->sesionClienteRepository = $sesionClienteRepository;
        $this->ClienteRepository = $ClienteRepository;
    }

    // Crear cliente
    public function store(Request $request)
    {
        try {
            // Validación de los datos
            $request->validate([
                'nombre' => 'required|string|max:100',
                'cedula' => 'required|string|max:20|unique:clientes,cedula',
                'telefono' => 'required|string|max:20|unique:clientes,telefono',
                'password' => 'required|string|min:6',
                'nickname' => 'required|string|max:100|unique:clientes,nickname'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Crear cliente usando el repositorio
            $cliente = $this->ClienteRepository->create($request->all());

            return response()->json([
                'message' => 'Cliente creado exitosamente',
                'cliente' => $cliente
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al crear el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar cliente
    public function update(Request $request, $id)
    {
        try {
            // Obtener el cliente a actualizar
            $cliente = $this->ClienteRepository->findById($id);
            if (!$cliente) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            // Validación de los datos para actualizar
            $request->validate([
                'nombre' => 'required|string|max:100',
                'cedula' => 'required|string|max:20',
                'telefono' => 'required|string|max:20|unique:clientes,telefono,' . $id, // Permitir el teléfono actual
                'password' => 'nullable|string|min:6',
                'nickname' => 'required|string|max:100|unique:clientes,nickname,' . $id, // Permitir el nickname actual
                'estado' => 'required|in:ACTIVO,INACTIVO',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            // Actualizar cliente usando el repositorio
            $cliente = $this->ClienteRepository->update($cliente, $request->all());

            return response()->json([
                'message' => 'Cliente actualizado exitosamente',
                'cliente' => $cliente
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al actualizar el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Listar todos los clientes
    public function index()
    {
        try {
            $clientes = $this->ClienteRepository->all();
            return response()->json([
                'clientes' => $clientes
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al obtener los clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener un cliente por ID
    public function show($id)
    {
        try {
            $cliente = $this->ClienteRepository->findById($id);
            if (!$cliente) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            return response()->json([
                'cliente' => $cliente
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al obtener el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un cliente
    public function destroy($id)
    {
        try {
            $cliente = $this->ClienteRepository->findById($id);
            if (!$cliente) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            // Eliminar cliente usando el repositorio
            $this->ClienteRepository->delete($cliente);

            return response()->json([
                'message' => 'Cliente eliminado exitosamente'
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error al eliminar el cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {

        try {
            // Validación directa de los datos de la solicitud
            $request->validate([
                'nickname' => 'required|string',
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            // Captura las excepciones de validación y devuelve los errores en formato JSON
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        // Buscar el cliente por el nickname
        $cliente = Cliente::where('nickname', $request->nickname)->first();

        // Verificar si el cliente existe y si la contraseña es correcta
        if (!$cliente || !Hash::check($request->password, $cliente->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // Si la validación pasa, creamos el token (esto es solo un ejemplo, puedes usar otros métodos)
        $token = bin2hex(random_bytes(30));

        try {
            // Registrar el token en la tabla 'sesiones_clientes'
            $this->sesionClienteRepository->createSesion($cliente->id, $token);
        } catch (QueryException $e) {
            // Capturar cualquier error de la base de datos (ejemplo: violación de clave foránea)
            return response()->json(['message' => 'Error al registrar la sesión del cliente', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Cliente autenticado',
            'token' => $token,
        ]);
    }

    public function getProfile(Request $request)
    {
        // Acceder al cliente autenticado
        $cliente = $request->user;

        return response()->json([
            'cliente' => $cliente,
        ]);
    }

    public function logout(Request $request)
    {
        // Eliminar la sesión del cliente
        $this->sesionClienteRepository->deleteSesionByToken($request->header('Authorization'));

        return response()->json(['message' => 'Cliente desconectado']);
    }
}
