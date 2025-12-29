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
    Schema::create('gastos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        $table->string('concepto');
        $table->decimal('monto', 12, 2);
        $table->date('fecha');

        $table->enum('metodo_pago', ['efectivo', 'transferencia', 'tarjeta']);
        $table->string('comprobante')->nullable();

        $table->text('observacion')->nullable();
        $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
