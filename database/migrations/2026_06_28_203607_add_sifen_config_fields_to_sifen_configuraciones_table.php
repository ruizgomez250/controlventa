<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sifen_configuraciones', function (Blueprint $table) {
            $table->string('calle_principal', 255)->nullable()->after('direccion');
            $table->string('numero_casa', 20)->nullable()->after('calle_principal');
            $table->string('calle_secundaria', 255)->nullable()->after('numero_casa');
            $table->string('complemento_direccion', 255)->nullable()->after('calle_secundaria');
            $table->integer('departamento_codigo')->default(1)->after('email');
            $table->integer('distrito_codigo')->default(1)->after('departamento_codigo');
            $table->integer('ciudad_codigo')->default(1)->after('distrito_codigo');
            $table->string('nombre_sucursal', 255)->nullable()->after('ciudad_codigo');
            $table->integer('tipo_contribuyente')->default(2)->after('nombre_sucursal');
            $table->integer('tipo_regimen')->nullable()->after('tipo_contribuyente');
            $table->integer('actividad_economica_codigo')->default(620)->after('tipo_regimen');
            $table->string('actividad_economica_descripcion', 500)->nullable()->after('actividad_economica_codigo');
            $table->string('csc_id', 10)->default('0001')->after('certificado_password');
            $table->string('csc_codigo', 40)->nullable()->after('csc_id');
        });
    }

    public function down(): void
    {
        Schema::table('sifen_configuraciones', function (Blueprint $table) {
            $table->dropColumn([
                'calle_principal',
                'numero_casa',
                'calle_secundaria',
                'complemento_direccion',
                'departamento_codigo',
                'distrito_codigo',
                'ciudad_codigo',
                'nombre_sucursal',
                'tipo_contribuyente',
                'tipo_regimen',
                'actividad_economica_codigo',
                'actividad_economica_descripcion',
                'csc_id',
                'csc_codigo',
            ]);
        });
    }
};
