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
        Schema::create('compras_cab', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_emision');
            $table->string('nro_factura',15); 
            $table->string('timbrado',15);           
            $table->foreignId('id_proveedor')->constrained('proveedores');            
            $table->string('condicion_de_compra',15); //compra contado credito
            $table->decimal('total_compra')->defatul(0);
            $table->foreignId('id_estado')->constrained('opciones'); 
            $table->foreignId('id_usuario')->constrained('users'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras_cab');
    }
};
