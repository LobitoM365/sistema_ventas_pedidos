<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SesionAdministrador;

class VerifyAdministradorAuth
{
    public function handle(Request $request, Closure $next)
    {
        //Extraer el token de los headers
        $token = $request->header('Authorization');

        //Si no tiene token retornar rspuesta inautorizada
        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }

        //Si hay token en los headers, buscar el mismo en las sesiones actuales
        $session = SesionAdministrador::where('token', $token)->first();

        //Si la sesion con ese token se encuentra registrada añadir al request la clave 'user' con la información del mismo
        if ($session) {
            $administrador = $session->administrador;
            $request->merge(['user' => $administrador]);
            return $next($request);
        }

        //Si no tiene una sesion activa el token retornar respuesta inautorizada
        return response()->json(['message' => 'Token inválido o expirado'], 401);
    }
}
