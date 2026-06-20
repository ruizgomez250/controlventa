<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagare', function (Blueprint $table) {
            $table->unsignedBigInteger('id_venta')->nullable()->change();
            $table->unsignedBigInteger('id_compra')->nullable()->after('id_venta');
            $table->foreign('id_compra')->references('id')->on('compras_cab')->onDelete('cascade');
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_compra')->nullable()->after('id_venta');
            $table->foreign('id_compra')->references('id')->on('compras_cab')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropForeign(['id_compra']);
            $table->dropColumn('id_compra');
        });

        Schema::table('pagare', function (Blueprint $table) {
            $table->dropForeign(['id_compra']);
            $table->dropColumn('id_compra');
            $table->unsignedBigInteger('id_venta')->nullable(false)->change();
        });
    }
};
