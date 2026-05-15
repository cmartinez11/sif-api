<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = \App\Models\Cotizacion::all();
foreach ($all as $c) {
    echo "ID: {$c->id}, Numero: {$c->numero}, Cliente: {$c->cliente_id}, Total: {$c->total}\n";
}
