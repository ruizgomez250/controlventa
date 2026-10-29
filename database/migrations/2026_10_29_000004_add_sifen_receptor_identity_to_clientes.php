<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('es_contribuyente')->default(true)->after('dv');
            $table->unsignedTinyInteger('tipo_identificacion')->nullable()->after('es_contribuyente');
            $table->string('numero_identificacion', 50)->nullable()->after('tipo_identificacion');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['es_contribuyente', 'tipo_identificacion', 'numero_identificacion']);
        });
    }
};
