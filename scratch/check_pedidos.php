<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ids = [127, 128, 129];
foreach ($ids as $id) {
    $pCount = \App\Models\Pedido::where('cotizacion_id', $id)->count();
    echo "Cotizacion ID $id has $pCount pedidos.\n";
}
