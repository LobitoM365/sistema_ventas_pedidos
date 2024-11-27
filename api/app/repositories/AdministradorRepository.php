<?php

namespace App\Repositories;

use App\Models\Administrador;

class AdministradorRepository
{
    // Obtener todos los administradores
    public function all()
    {
        return Administrador::all();
    }

    // Obtener un administrador por ID
    public function find($id)
    {
        return Administrador::findOrFail($id);
    }

    // Crear un nuevo administrador
    public function create(array $data)
    {
        return Administrador::create($data);
    }

    // Actualizar un administrador existente
    public function update($id, array $data)
    {
        $administrador = Administrador::findOrFail($id);
        $administrador->update($data);
        return $administrador;
    }

    // Eliminar un administrador
    public function delete($id)
    {
        $administrador = Administrador::findOrFail($id);
        return $administrador->delete();
    }
}
