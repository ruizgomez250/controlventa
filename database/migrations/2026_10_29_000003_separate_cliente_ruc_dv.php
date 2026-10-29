<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $separados = [];
        foreach (DB::table('clientes')->whereNotNull('ruc')->get(['id', 'ruc']) as $cliente) {
            if (preg_match('/^\s*(\d{3,8})\s*-\s*(\d)\s*$/', $cliente->ruc, $partes)) {
                if (DB::table('clientes')->where('ruc', $partes[1])->where('id', '!=', $cliente->id)->exists()) {
                    throw new RuntimeException('Hay clientes duplicados para el RUC ' . $partes[1] . '. Revise los registros antes de separar el DV.');
                }
                $separados[] = ['id' => $cliente->id, 'ruc' => $partes[1], 'dv' => $partes[2]];
            }
        }
        Schema::table('clientes', fn (Blueprint $table) => $table->string('dv', 1)->nullable()->after('ruc'));
        DB::transaction(function () use ($separados) {
            foreach ($separados as $cliente) {
                DB::table('clientes')->where('id', $cliente['id'])->update(['ruc' => $cliente['ruc'], 'dv' => $cliente['dv']]);
            }
        });
    }

    public function down(): void
    {
        DB::table('clientes')->whereNotNull('dv')->orderBy('id')->each(function ($cliente) {
            DB::table('clientes')->where('id', $cliente->id)->update(['ruc' => $cliente->ruc . '-' . $cliente->dv]);
        });
        Schema::table('clientes', fn (Blueprint $table) => $table->dropColumn('dv'));
    }
};
