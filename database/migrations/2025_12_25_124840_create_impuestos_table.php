<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->decimal('valor', 5, 2); // Ej: 10.00, 5.00, 0.00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impuestos');
    }
};
