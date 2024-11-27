<?php

namespace App\Repositories;

use App\Models\SesionCliente;

class SesionClienteRepository
{
    // Crear una nueva sesión para el cliente
    public function createSesion($clienteId, $token)
    {
        return SesionCliente::create([
            'clientes_id' => $clienteId,
            'token' => $token,
        ]);
    }

    // Obtener la sesión de un cliente a partir del token
    public function getSesionByToken($token)
    {
        return SesionCliente::where('token', $token)->first();
    }

    // Eliminar la sesión de un cliente utilizando el token
    public function deleteSesionByToken($token)
    {
        return SesionCliente::where('token', $token)->delete();
    }

    // Obtener todas las sesiones de un cliente
    public function getAllSesionesByCliente($clienteId)
    {
        return SesionCliente::where('clientes_id', $clienteId)->get();
    }
}
