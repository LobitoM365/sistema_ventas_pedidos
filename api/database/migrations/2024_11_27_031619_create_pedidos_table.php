<?php

// database/migrations/xxxx_xx_xx_create_pedidos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['PENDIENTE', 'ENTREGADO'])->default('PENDIENTE');
            $table->timestamps(0);
            $table->foreignId('clientes_id')->constrained('clientes');
            $table->foreignId('administradores_id')->constrained('administradores');
            $table->foreignId('ventas_id')->nullable()->constrained('ventas');
            $table->integer('cobrado');
            $table->string('direccion');
            $table->timestamp('fecha_entrega')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
}
