<?php

namespace App\Repositories;

use App\Models\SesionAdministrador;

class SesionAdministradorRepository
{
    // Crear una nueva sesión para el administrador
    public function createSesion($administradorId, $token)
    {
        return SesionAdministrador::create([
            'administradores_id' => $administradorId,
            'token' => $token,
        ]);
    }

    // Obtener la sesión de un administrador a partir del token
    public function getSesionByToken($token)
    {
        return SesionAdministrador::where('token', $token)->first();
    }

    // Eliminar la sesión de un administrador utilizando el token
    public function deleteSesionByToken($token)
    {
        return SesionAdministrador::where('token', $token)->delete();
    }

    // Obtener todas las sesiones de un administrador
    public function getAllSesionesByAdministrador($administradorId)
    {
        return SesionAdministrador::where('administradores_id', $administradorId)->get();
    }
}
