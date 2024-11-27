<?php

namespace App\Http\Controllers;

use App\Repositories\SesionClienteRepository;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 
 * @OA\Schema(
 *     schema="Cliente",
 *     type="object",
 *     required={"id", "nombre", "cedula", "telefono", "nickname", "estado", "password", "fecha_creacion"},
 *     @OA\Property(property="id", type="integer", description="ID del cliente"),
 *     @OA\Property(property="estado", type="string", enum={"ACTIVO", "INACTIVO"}, description="Estado del cliente"),
 *     @OA\Property(property="fecha_creacion", type="string", format="date-time", description="Fecha de creación del cliente"),
 *     @OA\Property(property="fecha_actualizacion", type="string", format="date-time", description="Fecha de última actualización del cliente"),
 *     @OA\Property(property="nombre", type="string", description="Nombre del cliente"),
 *     @OA\Property(property="cedula", type="string", description="Cédula del cliente"),
 *     @OA\Property(property="telefono", type="string", description="Teléfono del cliente"),
 *     @OA\Property(property="password", type="string", description="Contraseña del cliente (no se debe enviar en respuestas)"),
 *     @OA\Property(property="nickname", type="string", description="Nombre de usuario del cliente")
 * )
 */
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
    /**
     * @OA\Post(
     *     path="api/administradores/clientes",
     *     summary="Crear un nuevo cliente",
     *     tags={"Clientes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "cedula", "telefono", "password", "nickname"},
     *             @OA\Property(property="nombre", type="string", description="Nombre del cliente"),
     *             @OA\Property(property="cedula", type="string", description="Cédula del cliente"),
     *             @OA\Property(property="telefono", type="string", description="Teléfono del cliente"),
     *             @OA\Property(property="password", type="string", description="Contraseña del cliente"),
     *             @OA\Property(property="nickname", type="string", description="Nombre de usuario del cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cliente creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cliente creado exitosamente"),
     *             @OA\Property(property="cliente", ref="#/components/schemas/Cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al crear el cliente"
     *     )
     * )
     */
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
    /**
     * @OA\Put(
     *     path="api/administradores/clientes/{id}",
     *     summary="Actualizar los datos de un cliente",
     *     tags={"Clientes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del cliente a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "cedula", "telefono", "estado", "nickname"},
     *             @OA\Property(property="nombre", type="string", description="Nombre del cliente"),
     *             @OA\Property(property="cedula", type="string", description="Cédula del cliente"),
     *             @OA\Property(property="telefono", type="string", description="Teléfono del cliente"),
     *             @OA\Property(property="estado", type="string", enum={"ACTIVO", "INACTIVO"}, description="Estado del cliente"),
     *             @OA\Property(property="nickname", type="string", description="Nombre de usuario del cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cliente actualizado exitosamente"),
     *             @OA\Property(property="cliente", ref="#/components/schemas/Cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al actualizar el cliente"
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="api/administradores/clientes",
     *     summary="Obtener todos los clientes",
     *     tags={"Clientes"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de clientes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener los clientes"
     *     )
     * )
     */
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
    /**
     * @OA\Get(
     *     path="api/administradores/clientes/{id}",
     *     summary="Obtener un cliente por ID",
     *     tags={"Clientes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del cliente a obtener",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Datos del cliente",
     *         @OA\JsonContent(ref="#/components/schemas/Cliente")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al obtener el cliente"
     *     )
     * )
     */
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
    /**
     * @OA\Delete(
     *     path="api/administradores/clientes/{id}",
     *     summary="Eliminar un cliente",
     *     tags={"Clientes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del cliente a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cliente eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar el cliente"
     *     )
     * )
     */
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

    // Login del cliente
    /**
     * @OA\Post(
     *     path="api/administradores/clientes/login",
     *     summary="Iniciar sesión de un cliente",
     *     tags={"Clientes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nickname", "password"},
     *             @OA\Property(property="nickname", type="string", description="Nombre de usuario del cliente"),
     *             @OA\Property(property="password", type="string", description="Contraseña del cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cliente autenticado"),
     *             @OA\Property(property="token", type="string", description="Token JWT generado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al autenticar al cliente"
     *     )
     * )
     */
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

    // Obtener el perfil del cliente autenticado
    /**
     * @OA\Get(
     *     path="api/administradores/clientes/profile",
     *     summary="Obtener el perfil del cliente autenticado",
     *     tags={"Clientes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Datos del cliente",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Cliente"
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function getProfile(Request $request)
    {
        // Acceder al cliente autenticado
        $cliente = $request->user;

        return response()->json([
            'cliente' => $cliente,
        ]);
    }

    // Logout del cliente
    /**
     * @OA\Post(
     *     path="api/administradores/clientes/logout",
     *     summary="Cerrar sesión del cliente",
     *     tags={"Clientes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cliente desconectado"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        // Eliminar la sesión del cliente
        $this->sesionClienteRepository->deleteSesionByToken($request->header('Authorization'));

        return response()->json(['message' => 'Cliente desconectado']);
    }
}
