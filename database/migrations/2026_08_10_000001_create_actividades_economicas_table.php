<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actividades_economicas', function (Blueprint $table) {
            $table->integer('codigo')->primary();
            $table->string('descripcion', 500);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('actividades_economicas');
    }
};
