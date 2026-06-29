<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sifen_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('ruc_emisor', 20)->nullable();
            $table->string('dv', 3)->nullable();
            $table->string('razon_social', 255)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('establecimiento', 4)->default('001');
            $table->string('punto_expedicion', 4)->default('001');
            $table->integer('ambiente')->default(1);
            $table->text('certificado_p12')->nullable();
            $table->string('certificado_password', 255)->nullable();
            $table->boolean('habilitado')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sifen_configuraciones');
    }
};
