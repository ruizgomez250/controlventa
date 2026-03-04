<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {

            $table->foreignId('id_impuesto')
                ->after('impuesto') // opcional, para orden
                ->default(1)
                ->constrained('impuestos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {

            $table->dropForeign(['id_impuesto']);
            $table->dropColumn('id_impuesto');
        });
    }
};

