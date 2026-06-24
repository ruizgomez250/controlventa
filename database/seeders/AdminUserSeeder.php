<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $permissions = Permission::where('guard_name', 'web')->get();

        $role->syncPermissions($permissions);

        $user = User::updateOrCreate(
            [
                'email' => 'admin@controlventa.com',
            ],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$role]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Usuario administrador creado/actualizado con todos los permisos.');
        $this->command->info('Email: admin@controlventa.com');
        $this->command->info('Password: 12345678');
    }
}
