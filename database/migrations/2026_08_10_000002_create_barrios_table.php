<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barrios', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo');
            $table->string('nombre', 200);
            $table->unsignedBigInteger('ciudad_id');
            $table->foreign('ciudad_id')->references('id')->on('ciudades')->onDelete('cascade');
            $table->unique(['codigo', 'ciudad_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('barrios');
    }
};
