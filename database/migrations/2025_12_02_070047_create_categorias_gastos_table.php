<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categorias_gastos', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // ✅ IMPORTANTE para claves foráneas

            $table->id(); // BIGINT UNSIGNED
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categorias_gastos');
    }
};
