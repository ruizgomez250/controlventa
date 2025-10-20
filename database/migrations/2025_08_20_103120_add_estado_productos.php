<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Si existe la foreign key sobre id_estado, la eliminamos
            if (Schema::hasColumn('productos', 'id_estado')) {
                $table->dropForeign(['id_estado']); // 🔑 elimina la FK
                $table->dropColumn('id_estado');   // 🗑️ elimina la columna
            }

            // Agregar la columna estado si no existe
            if (!Schema::hasColumn('productos', 'estado')) {
                $table->integer('estado')->after('impuesto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Volver a poner id_estado con la foreign key (ajústalo al nombre de la tabla referenciada)
            if (!Schema::hasColumn('productos', 'id_estado')) {
                $table->unsignedBigInteger('id_estado')->nullable();
                $table->foreign('id_estado')->references('id')->on('estados'); // ⚡ ajusta "estados" al nombre real
            }

            // Quitar la columna estado si existe
            if (Schema::hasColumn('productos', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};
