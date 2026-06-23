<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('accion');
            $table->string('entidad_tipo');
            $table->unsignedBigInteger('entidad_id');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['entidad_tipo', 'entidad_id']);
            $table->index('accion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditoria');
    }
};
