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
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', ['cobrar', 'pagar']); // tipo de cheque

            $table->string('numero_cheque')->nullable();
            $table->string('banco')->nullable();
            $table->string('titular')->nullable();

            $table->decimal('monto', 12, 2);

            $table->date('fecha_emision')->nullable();
            $table->date('fecha_cobro'); // fecha importante

            $table->string('estado')->default('pendiente');
            // pendiente | cobrado | pagado | rechazado

            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
