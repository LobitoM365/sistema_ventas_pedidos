<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Administrador;
use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        Administrador::create([
            'nickname' => 'admin',
            'password' => 'admin123',
        ]);


        Cliente::create([
            'nombre' => 'Juan Pérez',
            'cedula' => '1234567890',
            'telefono' => '123456789',
            'nickname' => 'juanperez',
            'password' => 'cliente123'
        ]);
    }
}
