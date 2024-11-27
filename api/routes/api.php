<?php

use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


///Rutas para clientes -------------------------------------------------------------------------------------------------------
Route::prefix('clientes')->group(function () {
    // Rutas para clientes no autenticados (guest)
    Route::middleware('verify.cliente.guest')->group(function () {
        Route::post('login', [ClienteController::class, 'login']);
    });

    // Rutas protegidas para clientes autenticados (auth)
    Route::middleware('verify.cliente.auth')->group(function () {
        Route::get('profile', [ClienteController::class, 'getProfile']);
        Route::post('logout', [ClienteController::class, 'logout']);
    });
});


// Rutas para administradores ------------------------------------------------------------------------------------------------
Route::prefix('administradores')->group(function () {

    // Rutas para administradores no autenticados (guest)
    Route::middleware('verify.administrador.guest')->group(function () {
        // Ruta para el login de administrador (solo si no está logueado)
        Route::post('login', [AdministradorController::class, 'login']);
    });

    // Rutas protegidas para administradores autenticados (auth)
    Route::middleware('verify.administrador.auth')->group(function () {
        // Ruta para obtener el perfil del administrador
        Route::get('profile', [AdministradorController::class, 'getProfile']);

        // Ruta para hacer logout
        Route::post('logout', [AdministradorController::class, 'logout']);

        ////Rutas para usuarios desde admin
        Route::apiResource('clientes', ClienteController::class);

        ////Rutas para productos desde admin
        Route::prefix('productos')->group(function () {
            Route::post('/registrar', [ProductoController::class, 'store']);
            Route::get('/listar', [ProductoController::class, 'index']);
            Route::get('/buscar/{id}', [ProductoController::class, 'show']);
            Route::put('/actualizar/{id}', [ProductoController::class, 'update']);
            Route::delete('/eliminar/{id}', [ProductoController::class, 'destroy']);
            Route::put('/add/stock/{id}', [ProductoController::class, 'addStock'])->name('productos.addStock');
            Route::put('/remove/stock/{id}', [ProductoController::class, 'removeStock'])->name('productos.removeStock');
        });

        ////Rutas para pedidos desde admin
        Route::prefix('pedidos')->group(function () {
            Route::post('/crear', [PedidoController::class, 'store']);
            Route::get('/listar', [PedidoController::class, 'index']);
            Route::get('/buscar/{id}', [PedidoController::class, 'show']);
            Route::put('/entregar/{id}', [PedidoController::class, 'deliver']);
        });


        ////Rutas para ventas desde admin
        Route::prefix('ventas')->group(function () {
            Route::post('/crear', [VentaController::class, 'store']);
            Route::get('/listar', [VentaController::class, 'index']);
            Route::get('/buscar/{id}', [VentaController::class, 'show']);
        });
    });
});
