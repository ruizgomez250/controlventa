<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->boolean('estado')->default(0);
            $table->string('observacion')->nullable();
            $table->timestamps();
        });
        // Insertar registro inicial con descripcion = 'ventas' y estado = 1
        DB::table('configuraciones')->insert([
            'descripcion' => 'ventas',
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuraciones');
    }
};
