<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sesiones_administradores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('administradores_id');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->longText('token')->nullable();
            $table->foreign('administradores_id')->references('id')->on('administradores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_administradores');
    }
};
