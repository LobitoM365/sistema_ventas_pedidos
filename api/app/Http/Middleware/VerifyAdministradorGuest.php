<?php

namespace App\Http\Middleware;

use App\Models\SesionAdministrador;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyAdministradorGuest
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if ($token) {
            // Verificar si el token existe en la tabla de sesiones de administradores
            $session = SesionAdministrador::where('token', $token)->first();

            // Si el token existe, significa que el administrador ya está autenticado
            if ($session) {
                return response()->json(['message' => 'Ya estás autenticado'], 400);
            }
        }

        // Si no hay token o el token no está registrado, permitir el acceso
        return $next($request);
    }
}
