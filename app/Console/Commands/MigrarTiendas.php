<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Services\TenantConnectionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrarTiendas extends Command
{
    protected $signature = 'tienda:migrar {empresa? : ID de la empresa existente} {--todas : Actualizar todas las empresas activas}';
    protected $description = 'Instala las tablas de tienda en las bases de datos de empresas existentes.';

    public function handle(TenantConnectionManager $connections): int
    {
        if (!$this->argument('empresa') && !$this->option('todas')) {
            $this->error('Indicá el ID de empresa o --todas. Para la instalación local usá artisan migrate.');
            return self::FAILURE;
        }
        $empresas = Empresa::activo()->when($this->argument('empresa'), fn ($q, $id) => $q->whereKey($id))->get();
        if ($empresas->isEmpty()) { $this->error('No se encontraron empresas activas.'); return self::FAILURE; }
        $resultado = self::SUCCESS;
        foreach ($empresas as $empresa) {
            try {
                $connections->connect($empresa);
                $codigo = Artisan::call('migrate', ['--database' => 'tenant', '--path' => 'database/migrations/2026_11_12_000001_create_tienda_module.php', '--force' => true]);
                if ($codigo !== 0) throw new \RuntimeException('Migración incompleta.');
                $this->info('Tienda instalada en ' . $empresa->dominio . '.');
            } catch (\Throwable $e) {
                $this->error('No se pudo actualizar la empresa ' . $empresa->id . '. Revisá su conexión y migraciones.');
                $resultado = self::FAILURE;
            } finally { $connections->central(); }
        }
        return $resultado;
    }
}
