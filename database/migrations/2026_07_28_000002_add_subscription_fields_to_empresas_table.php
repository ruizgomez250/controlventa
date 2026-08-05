<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('estado', 20)->default('activa')->after('activo')->index();
            $table->timestamp('suspendida_at')->nullable()->after('fecha_expiracion');
            $table->timestamp('eliminable_at')->nullable()->after('suspendida_at');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['estado', 'suspendida_at', 'eliminable_at']);
        });
    }
};
