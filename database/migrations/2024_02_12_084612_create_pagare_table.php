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
        Schema::create('pagare', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->decimal('monto', 10, 2);
            $table->unsignedBigInteger('id_venta');
            $table->date('fecha_pago')->nullable();
            $table->integer('caja')->nullable();
            $table->integer('estado');
            $table->timestamps();

            // Clave foránea
            $table->foreign('id_venta')->references('id')->on('ventas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagare');
    }
};
