<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public const MODULES = ['stampa', 'scansione', 'samba', 'adguard', 'calendario'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::MODULES as $module) {
            Permission::findOrCreate("modulo-{$module}");
        }

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions(Permission::all());
    }
}
