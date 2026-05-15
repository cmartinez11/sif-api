<?php
use App\Models\CotizacionItem;
use App\Models\Plantilla;

$plantilla = Plantilla::where('nombre', 'PETS')->first();
if (!$plantilla) {
    echo "Plantilla PETS no encontrada\n";
    exit;
}

$item = CotizacionItem::whereHas('cotizacion', function($q) use ($plantilla) {
    $q->where('plantilla_id', $plantilla->id);
})->latest()->first();

if (!$item) {
    echo "No se encontraron items para PETS\n";
} else {
    echo "ID Item: " . $item->id . "\n";
    echo "JSON: " . $item->campos_json . "\n";
}
