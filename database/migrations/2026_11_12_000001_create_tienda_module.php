<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tienda_configuraciones', function (Blueprint $t) {
            $t->unsignedInteger('id')->primary();
            $t->uuid('identificador')->unique();
            $t->string('nombre', 100)->default('MiniFashion');
            $t->string('descripcion', 250)->default('Encontrá ese próximo favorito para los más pequeños. Elegí tu estilo y armá tu pedido a tu ritmo.');
            $t->string('whatsapp', 20)->nullable();
            $t->string('color', 7)->default('#176b4a');
            $t->string('moneda', 10)->default('PYG');
            $t->string('aviso', 200)->default('Elegí tus favoritos y coordiná tu pedido por WhatsApp');
            $t->boolean('habilitada')->default(true);
            $t->timestamps();
        });
        Schema::create('tienda_productos', function (Blueprint $t) {
            $t->unsignedBigInteger('producto_id')->primary();
            $t->boolean('visible')->default(true);
            $t->boolean('premium')->default(false);
            $t->boolean('destacado')->default(false);
            $t->decimal('precio_oferta', 14, 2)->nullable();
            $t->dateTime('oferta_hasta')->nullable();
            $t->timestamps();
        });
        Schema::create('tienda_pedidos', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid')->unique();
            $t->uuid('referencia')->unique();
            $t->string('token_hash', 64);
            $t->string('solicitud_hash', 64);
            $t->string('nombre', 100);
            $t->string('telefono', 25);
            $t->string('entrega', 10);
            $t->string('direccion', 250)->nullable();
            $t->string('observaciones', 500)->nullable();
            $t->string('estado', 20)->default('pendiente')->index();
            $t->decimal('total', 14, 2);
            $t->string('moneda', 10);
            $t->json('items');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tienda_pedidos');
        Schema::dropIfExists('tienda_productos');
        Schema::dropIfExists('tienda_configuraciones');
    }
};
