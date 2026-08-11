<?php

namespace Database\Seeders;

use App\Helpers\ActividadesEconomicas;
use App\Models\ActividadEconomica;
use Illuminate\Database\Seeder;

class ActividadEconomicaSeeder extends Seeder
{
    public function run()
    {
        $actividades = ActividadesEconomicas::ACTIVIDADES;

        foreach ($actividades as $codigo => $descripcion) {
            ActividadEconomica::firstOrCreate(
                ['codigo' => $codigo],
                ['descripcion' => $descripcion]
            );
        }

        $this->command->info('Actividades económicas (CIUU) cargadas exitosamente: ' . count($actividades));
    }
}
