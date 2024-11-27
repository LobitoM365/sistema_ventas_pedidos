<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Repositories\SesionAdministradorRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**


 * @OA\Schema(
 *     schema="Administrador",
 *     type="object",
 *     required={"id", "nickname", "estado", "password", "fecha_creacion"},
 *     @OA\Property(property="id", type="integer", description="ID del administrador"),
 *     @OA\Property(property="estado", type="string", enum={"ACTIVO", "INACTIVO"}, description="Estado del administrador"),
 *     @OA\Property(property="fecha_creacion", type="string", format="date-time", description="Fecha de creación del administrador"),
 *     @OA\Property(property="fecha_actualizacion", type="string", format="date-time", description="Fecha de última actualización del administrador"),
 *     @OA\Property(property="nickname", type="string", description="Nombre de usuario del administrador"),
 *     @OA\Property(property="password", type="string", description="Contraseña del administrador (no se debe enviar en respuestas)")
 * )
 */
class AdministradorController extends Controller
{
    protected $sesionAdministradorRepository;

    public function __construct(SesionAdministradorRepository $sesionAdministradorRepository)
    {
        $this->sesionAdministradorRepository = $sesionAdministradorRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/admin/login",
     *     summary="Iniciar sesión de administrador",
     *     tags={"Administradores"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nickname", "password"},
     *             @OA\Property(property="nickname", type="string", description="Nombre de usuario del administrador"),
     *             @OA\Property(property="password", type="string", description="Contraseña del administrador")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Administrador autenticado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Administrador autenticado"),
     *             @OA\Property(property="token", type="string", description="Token de acceso del administrador")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
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

        // Buscar el administrador por el nickname
        $administrador = Administrador::where('nickname', $request->nickname)->first();

        // Verificar si el administrador existe y si la contraseña es correcta
        if (!$administrador || !Hash::check($request->password, $administrador->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // Si la validación pasa, creamos el token
        $token = bin2hex(random_bytes(30));

        try {
            // Registrar el token en la tabla 'sesiones_clientes'
            $this->sesionAdministradorRepository->createSesion($administrador->id, $token);
        } catch (QueryException $e) {
            // Capturar cualquier error de la base de datos
            return response()->json(['message' => 'Error al registrar la sesión del administrador', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Administrador autenticado',
            'token' => $token,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/profile",
     *     summary="Obtener el perfil del administrador autenticado",
     *     tags={"Administradores"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Perfil del administrador",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Administrador"
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
        // Acceder al administrador autenticado
        $administrador = $request->user();

        return response()->json([
            'administrador' => $administrador,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/logout",
     *     summary="Cerrar sesión del administrador",
     *     tags={"Administradores"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Administrador desconectado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Administrador desconectado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        // Eliminar la sesión del administrador
        $this->sesionAdministradorRepository->deleteSesionByToken($request->header('Authorization'));

        return response()->json(['message' => 'Administrador desconectado']);
    }
}
