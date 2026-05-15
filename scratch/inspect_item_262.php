<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$item = \App\Models\CotizacionItem::find(262);
if (!$item) {
    echo "Item 262 no encontrado\n";
    exit;
}

echo "ID Item: " . $item->id . "\n";
echo "Cotizacion ID: " . $item->cotizacion_id . "\n";
echo "Plantilla: " . $item->cotizacion->plantilla->nombre . "\n";
echo "JSON: " . $item->campos_json . "\n";
