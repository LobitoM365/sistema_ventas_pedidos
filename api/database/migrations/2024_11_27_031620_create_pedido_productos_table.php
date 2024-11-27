<?php
// database/migrations/xxxx_xx_xx_create_pedido_productos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoProductosTable extends Migration
{
    public function up()
    {
        Schema::create('pedido_productos', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamps(0);
            $table->foreignId('pedidos_id')->constrained('pedidos');
            $table->foreignId('productos_id')->constrained('productos');
            $table->float('cantidad')->default(1);
            $table->float('costo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_productos');
    }
}
