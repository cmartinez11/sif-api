<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'Vendedor']);
        Role::create(['name' => 'Supervisor']);
        Role::create(['name' => 'Logistico']);
        Role::create(['name' => 'Administrador']);
    }
}
