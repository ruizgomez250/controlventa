<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('sifen_codigo', 10)->nullable();
            $table->text('sifen_mensaje')->nullable();
            $table->json('sifen_respuesta')->nullable();
            $table->timestamp('sifen_actualizado_at')->nullable();
            $table->unsignedTinyInteger('sifen_ambiente')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['sifen_codigo', 'sifen_mensaje', 'sifen_respuesta', 'sifen_actualizado_at', 'sifen_ambiente']);
        });
    }
};
