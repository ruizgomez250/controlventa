<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeExpiredTenants extends Command
{
    protected $signature = 'tenants:purge-expired {--force : Elimina definitivamente los tenants vencidos}';

    protected $description = 'Lista o elimina tenants suspendidos cuyo periodo de retención terminó';

    public function handle(): int
    {
        $empresas = Empresa::query()
            ->where('estado', 'suspendida')
            ->whereNotNull('eliminable_at')
            ->where('eliminable_at', '<=', now())
            ->get();

        if ($empresas->isEmpty()) {
            $this->info('No hay tenants listos para eliminación.');

            return self::SUCCESS;
        }

        foreach ($empresas as $empresa) {
            $this->line("{$empresa->id}: {$empresa->nombre} ({$empresa->database_name})");
        }

        if (! $this->option('force')) {
            $this->warn('Ejecución informativa. Usá --force para eliminar definitivamente.');

            return self::SUCCESS;
        }

        foreach ($empresas as $empresa) {
            if (
                ! preg_match('/^[a-z0-9_]+$/', $empresa->database_name)
                || ! preg_match('/^[a-z0-9_]+$/', $empresa->database_username)
            ) {
                $this->error("Se omitió {$empresa->id}: identificadores inválidos.");

                continue;
            }

            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$empresa->database_name}`");
            DB::connection('mysql')->statement("DROP USER IF EXISTS '{$empresa->database_username}'@'%'");
            $empresa->delete();
            $this->info("Tenant {$empresa->id} eliminado.");
        }

        return self::SUCCESS;
    }
}
