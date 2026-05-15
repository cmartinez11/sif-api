<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@plasticosfenix.com',
            'password' => bcrypt('password')
        ]);
        
        $admin->assignRole('Administrador');
    }
}
