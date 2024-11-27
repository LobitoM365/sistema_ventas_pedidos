<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable()->useCurrentOnUpdate();
            $table->string('nombre', 100);
            $table->string('cedula', 20);
            $table->string('telefono', 20)->unique();
            $table->string('password', 225);
            $table->string('nickname', 100)->unique();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
