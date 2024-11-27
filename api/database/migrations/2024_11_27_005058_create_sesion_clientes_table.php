<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sesiones_clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clientes_id');
            $table->foreign('clientes_id')->references('id')->on('clientes');
            $table->longText('token')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sesiones_clientes'); // Eliminar la tabla si existe
    }
};
