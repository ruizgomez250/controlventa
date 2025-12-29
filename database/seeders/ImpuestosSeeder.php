<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImpuestosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('impuestos')->insert([
            [
                'descripcion' => 'I.V.A.',
                'valor' => 10.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'I.V.A.',
                'valor' => 5.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'descripcion' => 'EXENTA',
                'valor' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
