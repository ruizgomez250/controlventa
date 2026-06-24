<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('stock_inicial', 10, 3)->nullable()->default(0)->after('stock_minimo');
            $table->decimal('stock_maximo', 10, 3)->nullable()->default(0)->after('stock_inicial');
            $table->string('ubicacion_deposito', 255)->nullable()->after('stock_maximo');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['stock_inicial', 'stock_maximo', 'ubicacion_deposito']);
        });
    }
};
