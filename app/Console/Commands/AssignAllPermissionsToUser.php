<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class AssignAllPermissionsToUser extends Command
{
    protected $signature = 'permissions:assign-all-to-user {user?} {--all : Assign permissions to ALL users}';
    protected $description = 'Assign all existing permissions to one or all users';

    public function handle()
    {
        $permissions = Permission::all();

        if ($permissions->isEmpty()) {
            $this->error('No hay permisos en la base de datos. Ejecute php artisan db:seed --class=PermissionSeeder primero.');
            return 1;
        }

        if ($this->option('all')) {
            $users = User::all();
            $bar = $this->output->createProgressBar(count($users));
            $bar->start();

            foreach ($users as $user) {
                $user->syncPermissions($permissions);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info(count($users) . ' usuarios recibieron ' . count($permissions) . ' permisos cada uno.');
            return 0;
        }

        $userId = $this->argument('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuario con ID {$userId} no encontrado.");
                return 1;
            }
        } else {
            $email = $this->ask('Ingrese el email del usuario');
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("Usuario con email {$email} no encontrado.");
                return 1;
            }
        }

        $user->syncPermissions($permissions);

        $this->info(count($permissions) . " permisos asignados a {$user->name} ({$user->email}).");
    }
}
