<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrega_insumos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('id_persona')->constrained('personas');
            $table->text('observacion')->nullable();
            $table->foreignId('id_usuario')->constrained('users');
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrega_insumos');
    }
};
