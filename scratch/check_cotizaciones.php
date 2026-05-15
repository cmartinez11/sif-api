<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latest = \App\Models\Cotizacion::orderBy('id', 'desc')->take(5)->get();
echo "Latest Cotizaciones:\n";
foreach ($latest as $c) {
    echo "ID: {$c->id}, Numero: {$c->numero}\n";
}

$count = \App\Models\Cotizacion::count();
echo "Total Cotizaciones: $count\n";
