<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->integer('codigo')->primary();
            $table->string('nombre', 100);
            $table->timestamps();
        });

        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo');
            $table->string('nombre', 200);
            $table->integer('departamento_codigo');
            $table->foreign('departamento_codigo')->references('codigo')->on('departamentos')->onDelete('cascade');
            $table->unique(['codigo', 'departamento_codigo']);
            $table->timestamps();
        });

        Schema::create('ciudades', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo');
            $table->string('nombre', 200);
            $table->unsignedBigInteger('distrito_id');
            $table->foreign('distrito_id')->references('id')->on('distritos')->onDelete('cascade');
            $table->unique(['codigo', 'distrito_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ciudades');
        Schema::dropIfExists('distritos');
        Schema::dropIfExists('departamentos');
    }
};
