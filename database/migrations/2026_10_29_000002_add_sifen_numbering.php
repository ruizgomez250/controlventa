<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sifen_configuraciones', function (Blueprint $table) {
            $table->string('timbrado', 8)->nullable();
            $table->date('fecha_inicio_timbrado')->nullable();
            $table->unsignedInteger('ultimo_numero_factura')->default(0);
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->date('sifen_fecha_inicio_timbrado')->nullable();
            $table->string('sifen_establecimiento', 3)->nullable();
            $table->string('sifen_punto_expedicion', 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', fn (Blueprint $table) => $table->dropColumn(['sifen_fecha_inicio_timbrado', 'sifen_establecimiento', 'sifen_punto_expedicion']));
        Schema::table('sifen_configuraciones', fn (Blueprint $table) => $table->dropColumn(['timbrado', 'fecha_inicio_timbrado', 'ultimo_numero_factura']));
    }
};
