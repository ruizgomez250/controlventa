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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('razonsocial',70);
            $table->string('ruc',14)->unique()->nullable();
            $table->string('direccion',150)->nullable();
            $table->string('correo',50)->nullable();
            $table->string('telefono',30)->nullable();
            $table->string('celular',12)->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('id_estado')
            ->nullable()
            ->constrained('opciones')
            ->cascadeOnUpdate()
            ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
