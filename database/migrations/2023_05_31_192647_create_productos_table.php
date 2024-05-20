<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cuentan con 11 dígitos de datos y uno de verificación (un total de 12). 
     * El primer dígito representa el tipo de producto que se está identificando.
     * Los cinco dígitos siguientes son el código del fabricante,
     * y los siguientes cinco dígitos posteriores sirven para identificar un producto específico
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo',14)->unique();
            $table->string('descripcion',150);
            $table->text('detalle')->nullable();
            $table->integer('impuesto');
          $table->foreignId('id_categoria')
            ->nullable()
            ->constrained('opciones')
            ->cascadeOnUpdate()
            ->nullOnDelete();
            $table->decimal('stock', 10, 3)->default(0);
            $table->foreignId('id_medida')
            ->nullable()
            ->constrained('opciones')
            ->cascadeOnUpdate()
            ->nullOnDelete();
            $table->decimal('pcosto', 10, 0)->default(0)->nullable();
            $table->decimal('pventa', 10, 0)->default(0)->nullable();
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
        Schema::dropIfExists('productos');
    }
};
