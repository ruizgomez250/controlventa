<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Console\Command;

class MigrateOldPermissions extends Command
{
    protected $signature = 'permissions:migrate-old';
    protected $description = 'Migrate old roles table data to Spatie permissions';

    public function handle()
    {
        $oldRoles = Rol::all();
        $count = 0;

        foreach ($oldRoles as $rol) {
            $user = User::find($rol->id_usuario);
            if (!$user) continue;

            $model = strtolower($rol->nombre_modelo);

            if ($rol->leer) {
                $user->givePermissionTo($model . ' leer');
            }
            if ($rol->crear) {
                $user->givePermissionTo($model . ' crear');
            }
            if ($rol->editar) {
                $user->givePermissionTo($model . ' editar');
            }
            if ($rol->borrar) {
                $user->givePermissionTo($model . ' borrar');
            }

            $count++;
        }

        $this->info("Migrated {$count} old role records to Spatie permissions.");
    }
}
