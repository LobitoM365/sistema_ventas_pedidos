<?php

namespace App\Repositories;

use App\Models\Cliente;
use App\Models\SesionCliente;
use Carbon\Carbon;

class ClienteRepository
{

    public function createSesion($clienteId, $token)
    {
        return SesionCliente::create([
            'clientes_id' => $clienteId,
            'token' => $token,
        ]);
    }

    public function create(array $data)
    {
        return Cliente::create($data);
    }

    public function update(Cliente $cliente, array $data)
    {
        $cliente->update($data);
        return $cliente;
    }

    public function findById(int $id)
    {
        return Cliente::find($id);
    }

    public function findByPhone(string $telefono)
    {
        return Cliente::where('telefono', $telefono)->first();
    }

    public function all()
    {
        return Cliente::all();
    }

    public function delete(Cliente $cliente)
    {
        return $cliente->delete();
    }
}
