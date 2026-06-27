<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrega_insumo_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_entrega')->constrained('entrega_insumos');
            $table->foreignId('id_producto')->constrained('productos');
            $table->decimal('cantidad', 10, 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_insumo_detalles');
    }
};
