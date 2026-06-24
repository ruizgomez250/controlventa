<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImpuestosSeeder extends Seeder
{
    public function run(): void
    {
        $impuestos = [
            [
                'descripcion' => 'I.V.A.',
                'valor' => 10.00,
            ],
            [
                'descripcion' => 'I.V.A.',
                'valor' => 5.00,
            ],
            [
                'descripcion' => 'EXENTA',
                'valor' => 0.00,
            ],
        ];

        foreach ($impuestos as $impuesto) {
            DB::table('impuestos')->updateOrInsert(
                [
                    'descripcion' => $impuesto['descripcion'],
                    'valor' => $impuesto['valor'],
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->command->info('Impuestos verificados/creados correctamente.');
    }
}
