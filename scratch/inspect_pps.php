<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$item = \App\Models\CotizacionItem::whereHas('cotizacion', function($q) {
    $q->whereHas('plantilla', function($sq) { $sq->where('nombre', 'Bolsas de Polipropileno'); });
})->latest()->first();

if (!$item) {
    echo "No se encontraron items para Bolsas de Polipropileno\n";
} else {
    echo "ID Item: " . $item->id . "\n";
    echo "JSON: " . $item->campos_json . "\n";
}
