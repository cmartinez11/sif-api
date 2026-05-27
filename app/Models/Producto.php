<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'productos';
    protected $guarded = [];

    protected $casts = [
        'stock' => 'decimal:3',
        'deuda_arrastrada' => 'decimal:3',
        'ultimo_stock_cargado_at' => 'date',
        'precio_base' => 'decimal:2',
        'peso' => 'decimal:3',
        'estado' => 'boolean',
    ];

    protected $appends = ['saldo_disponible_sif'];

    /**
     * Calcula el Saldo Disponible SIF neto descontando el stock comprometido futuro
     */
    public function getSaldoDisponibleSifAttribute()
    {
        $fechaLimite = \Carbon\Carbon::tomorrow()->toDateString();
        $hoy = date('Y-m-d');

        // 1. Stock comprometido futuro (pedidos Aprobados o Pendientes con fecha despacho >= mañana)
        $totalComprometido = \Illuminate\Support\Facades\DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedido_items.producto_id', $this->id)
            ->whereIn('pedidos.estado', ['Aprobado', 'Pendiente'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->sum(\Illuminate\Support\Facades\DB::raw("COALESCE(
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                0
            )"));

        // 2. Ventas de hoy que se despachan a futuro (para evitar el doble descuento)
        $vendidoHoyFuturo = \Illuminate\Support\Facades\DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedido_items.producto_id', $this->id)
            ->where('pedidos.fecha_pedido', $hoy)
            ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->sum(\Illuminate\Support\Facades\DB::raw("COALESCE(
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                0
            )"));

        return ((float)$this->stock - (float)($this->deuda_arrastrada ?? 0.000)) - (float)$totalComprometido + (float)$vendidoHoyFuturo;
    }

    /**
     * Relación: Un producto puede tener muchos items de cotización
     */
    public function cotizacionItems()
    {
        return $this->hasMany(CotizacionItem::class);
    }
}
