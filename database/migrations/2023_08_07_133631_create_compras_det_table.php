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
        Schema::create('compras_det', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_compracab')->constrained('compras_cab');  
            $table->foreignId('id_productos')->constrained('productos');
            $table->string('descripcion');  
            $table->decimal('cantidad');
            $table->decimal('precio_u');
            $table->decimal('monto')->default(0);
            $table->decimal('tipo_impuesto')->default(0); //0  5 10
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras_det');
    }
};
