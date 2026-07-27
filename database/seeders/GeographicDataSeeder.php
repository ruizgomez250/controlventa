<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Ciudad;
use Illuminate\Database\Seeder;

class GeographicDataSeeder extends Seeder
{
    public function run()
    {
        $filePath = base_path('app/Models/distribuciongeografica.txt');

        if (!file_exists($filePath)) {
            $this->command->error("File not found: $filePath");
            return;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->command->error("Could not open file: $filePath");
            return;
        }

        fgetcsv($handle, 0, "\t");

        $departamentos = [];
        $distritos = [];
        $ciudades = [];

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (count($row) < 6) continue;

            $depCodigo = (int) trim($row[0]);
            $depNombre = trim($row[1]);
            $disCodigo = (int) trim($row[2]);
            $disNombre = trim($row[3]);
            $ciuCodigo = (int) trim($row[4]);
            $ciuNombre = trim($row[5]);

            if (!isset($departamentos[$depCodigo])) {
                $departamentos[$depCodigo] = $depNombre;
            }

            $distKey = $depCodigo . '_' . $disCodigo;
            if (!isset($distritos[$distKey])) {
                $distritos[$distKey] = [
                    'codigo' => $disCodigo,
                    'nombre' => $disNombre,
                    'departamento_codigo' => $depCodigo,
                ];
            }

            $ciuKey = $distKey . '_' . $ciuCodigo;
            if (!isset($ciudades[$ciuKey])) {
                $ciudades[$ciuKey] = [
                    'codigo' => $ciuCodigo,
                    'nombre' => $ciuNombre,
                    'distrito_key' => $distKey,
                ];
            }
        }

        fclose($handle);

        foreach ($departamentos as $codigo => $nombre) {
            Departamento::firstOrCreate(
                ['codigo' => $codigo],
                ['nombre' => $nombre]
            );
        }

        $distritoMap = [];
        foreach ($distritos as $key => $data) {
            $distrito = Distrito::firstOrCreate(
                ['codigo' => $data['codigo'], 'departamento_codigo' => $data['departamento_codigo']],
                ['nombre' => $data['nombre']]
            );
            $distritoMap[$key] = $distrito->id;
        }

        foreach ($ciudades as $data) {
            $distritoId = $distritoMap[$data['distrito_key']] ?? null;
            if ($distritoId) {
                Ciudad::firstOrCreate(
                    ['codigo' => $data['codigo'], 'distrito_id' => $distritoId],
                    ['nombre' => $data['nombre']]
                );
            }
        }

        $this->command->info('Datos geográficos cargados exitosamente!');
    }
}
