<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pedido;
use App\Models\Producto;

$pedidos = Pedido::with('items.producto')
    ->whereHas('items.producto', function($q) {
        $q->whereIn('codigo', ['PT10000006', 'PT10000007']);
    })->get();

foreach ($pedidos as $p) {
    echo "Pedido ID: " . $p->id . "\n";
    echo "Numero: " . $p->numero . "\n";
    echo "Estado: " . $p->estado . "\n";
    echo "Fecha Pedido: " . $p->fecha_pedido . "\n";
    echo "Fecha Entrega Confirmada: " . $p->fecha_entrega_confirmada . "\n";
    foreach ($p->items as $item) {
        if ($item->producto && in_array($item->producto->codigo, ['PT10000006', 'PT10000007'])) {
            echo "  Item ID: " . $item->id . "\n";
            echo "  Producto: " . $item->producto->codigo . "\n";
            echo "  Stock: " . $item->producto->stock . "\n";
            echo "  Campos JSON: " . json_encode($item->campos_json) . "\n";
        }
    }
    echo "---------------------------\n";
}

use Carbon\Carbon;

$fechaLimite = Carbon::tomorrow()->toDateString();
echo "Fecha limite (tomorrow): $fechaLimite\n";

$productos = Producto::whereIn('codigo', ['PT10000006', 'PT10000007'])->get();
foreach ($productos as $pr) {
    // 1. Total comprometido futuro
    $totalComprometido = DB::table('pedido_items')
        ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
        ->where('pedido_items.producto_id', $pr->id)
        ->whereIn('pedidos.estado', ['Aprobado', 'Pendiente'])
        ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
        ->sum(DB::raw("COALESCE(
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
            0
        )"));

    // 2. Ventas hoy futuro
    $hoy = date('Y-m-d');
    $vendidoHoyFuturo = DB::table('pedido_items')
        ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
        ->where('pedido_items.producto_id', $pr->id)
        ->where('pedidos.fecha_pedido', $hoy)
        ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
        ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
        ->sum(DB::raw("COALESCE(
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
            CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
            0
        )"));

    $formulaSimple = (float)$pr->stock - (float)$totalComprometido;
    $formulaCompensada = (float)$pr->stock - (float)$totalComprometido + (float)$vendidoHoyFuturo;

    echo "Producto: {$pr->codigo}\n";
    echo "  Stock actual: {$pr->stock}\n";
    echo "  Comprometido futuro: $totalComprometido\n";
    echo "  Vendido hoy futuro: $vendidoHoyFuturo\n";
    echo "  Formula Simple (stock - comprometido): $formulaSimple\n";
    echo "  Formula Compensada (stock - comprometido + vendidoHoyFuturo): $formulaCompensada\n";
    echo "  Atributo saldo_disponible_sif (Eloquent): {$pr->saldo_disponible_sif}\n";
    echo "---------------------------\n";
}

