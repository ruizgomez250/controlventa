<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('dominio')->unique();
            $table->string('database_name')->unique();
            $table->string('database_host');
            $table->string('database_port');
            $table->string('database_username');
            $table->text('database_password');
            $table->string('email_admin')->unique();
            $table->string('password_admin');
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_expiracion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
