<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('configuraciones')->insert([
            'descripcion' => 'moneda',
            'estado' => 1,
            'observacion' => 'Gs.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('configuraciones')->where('descripcion', 'moneda')->delete();
    }
};
