<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SesionCliente;

class VerifyClienteAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }

        $session = SesionCliente::where('token', $token)->first();

        if ($session) {
            $cliente = $session->cliente;
            $request->merge(['user' => $cliente]);

            return $next($request);
        }

        return response()->json(['message' => 'Token inválido o expirado'], 401);
    }
}
