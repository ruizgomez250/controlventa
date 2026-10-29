<?php

use App\Helpers\RucParaguayo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // 1 = persona física, 2 = persona jurídica (iTiContRec de SIFEN).
            $table->unsignedTinyInteger('tipo_contribuyente')->nullable()->after('es_contribuyente');
        });

        DB::table('clientes')->where('es_contribuyente', true)->whereNotNull('ruc')->orderBy('id')
            ->each(function ($cliente) {
                try {
                    $tipo = RucParaguayo::tipoContribuyente((string) $cliente->ruc);
                } catch (\InvalidArgumentException) {
                    return;
                }
                DB::table('clientes')->where('id', $cliente->id)->update(['tipo_contribuyente' => $tipo]);
            });

        // La cédula paraguaya se guardó con el DV del RUC (p. ej. 1807552-5).
        DB::table('clientes')->where('tipo_identificacion', 1)->whereNotNull('numero_identificacion')->orderBy('id')
            ->each(function ($cliente) {
                $numero = RucParaguayo::numeroDocumento(1, (string) $cliente->numero_identificacion);
                if ($numero !== $cliente->numero_identificacion) {
                    DB::table('clientes')->where('id', $cliente->id)->update(['numero_identificacion' => $numero]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('tipo_contribuyente');
        });
    }
};
