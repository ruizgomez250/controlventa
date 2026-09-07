<?php

namespace Database\Seeders;

use App\Models\ClothingSize;
use App\Models\GarmentType;
use Illuminate\Database\Seeder;

class OldNavyClothingCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Body', 'Mameluco', 'Conjunto de recién nacido', 'Body gráfico',
            'Remera gráfica', 'Camisa', 'Sudadera', 'Hoodie', 'Polo',
            'Jean', 'Legging', 'Jogger', 'Pantalón cargo', 'Short', 'Pantalón chino',
            'Vestido', 'Overol', 'Jardinera', 'Mono / Romper', 'Conjunto coordinado',
            'Pijama de una pieza', 'Pijama de dos piezas', 'Bata', 'Conjunto de dormir',
            'Chaqueta', 'Chamarra', 'Chaleco', 'Ropa de nieve',
            'Short deportivo', 'Pantalón deportivo', 'Camiseta técnica',
            'Traje de baño', 'Trunk de baño', 'Rash guard',
            'Polo de uniforme', 'Pantalón caqui', 'Prenda YOU-uniform',
            'Remera Disney / Pixar', 'Prenda de cumpleaños',
        ];
        foreach ($types as $type) GarmentType::firstOrCreate(['name' => $type]);

        $sizes = [
            'Prematuro', 'Recién nacido', '0-3 meses', '3-6 meses', '6-9 meses', '6-12 meses',
            '9-12 meses', '12-18 meses', '18-24 meses', '2T', '3T', '4T', '5T', '5', '6',
            '6-7', '7', '8', '10', '12', '14', '16', '18', 'XS', 'S', 'M', 'L', 'XL', 'XXL',
        ];
        foreach ($sizes as $order => $size) {
            ClothingSize::updateOrCreate(['name' => $size], ['sort_order' => $order + 1]);
        }
    }
}
