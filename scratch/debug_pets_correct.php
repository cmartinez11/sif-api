<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$plantilla = \App\Models\Plantilla::where('nombre', 'Pets')->first();
if (!$plantilla) {
    echo "Plantilla Pets no encontrada\n";
    exit;
}

$item = \App\Models\CotizacionItem::whereHas('cotizacion', function($q) use ($plantilla) {
    $q->where('plantilla_id', $plantilla->id);
})->latest()->first();

if (!$item) {
    echo "No se encontraron items para Pets\n";
} else {
    echo "ID Item: " . $item->id . "\n";
    echo "JSON: " . $item->campos_json . "\n";
}
