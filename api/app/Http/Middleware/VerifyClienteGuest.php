<?php

namespace App\Http\Middleware;

use App\Models\SesionCliente;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyClienteGuest
{
    public function handle(Request $request, Closure $next)
    {
        
        $token = $request->header('Authorization');

        if ($token) {
            // Verificar si el token existe en la tabla de sesiones de clientes
            $session = SesionCliente::where('token', $token)->first();

            // Si el token existe, significa que el cliente ya está autenticado
            if ($session) {
                return response()->json(['message' => 'Ya estás autenticado'], 400);
            }
        }

        return $next($request);
    }
}
