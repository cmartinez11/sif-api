<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder
{
    public function run()
    {
        DB::table('plantillas')->insert([
            ['nombre' => 'Tratadas'],
            ['nombre' => 'PPS'],
            ['nombre' => 'PETS'],
            ['nombre' => 'Universal'],
        ]);
    }
}
