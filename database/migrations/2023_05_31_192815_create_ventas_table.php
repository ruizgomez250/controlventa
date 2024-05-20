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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_cliente');
            $table->string('tipo_comprobante');
            $table->decimal('total', 10, 2);
            $table->date('fecha_emision');
            $table->string('numero_factura')->nullable();
            $table->string('timbrado_factura')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->integer('estado');
            // Agregar más campos según sea necesario

            $table->timestamps();

            // Definir claves foráneas
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreign('id_cliente')->references('id')->on('clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
