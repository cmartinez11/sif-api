<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pedido = \App\Models\Pedido::first();
if ($pedido) {
    print_r($pedido->getAttributes());
}

