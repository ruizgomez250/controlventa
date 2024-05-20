<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tabla_porcentajes', function (Blueprint $table) {
            $table->id();
            $table->decimal('porcentaje', 8, 2); // Campo para el porcentaje
            $table->decimal('cuota', 10, 2); // Campo para la cuota
            $table->boolean('estado')->default(true); // Campo para el estado (activo/inactivo)
            $table->timestamps(); // Campos para las marcas de tiempo created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tabla_porcentajes');
    }
};
