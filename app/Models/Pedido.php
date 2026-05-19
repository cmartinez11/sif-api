<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedidos';
    protected $guarded = [];

    /**
     * Obtener la clave de enrutamiento para el modelo.
     * Esto cambia las URLs de /pedidos/{id} a /pedidos/{numero}
     */
    public function getRouteKeyName()
    {
        return 'numero';
    }

    protected $casts = [
        'cantidades_json' => 'array',
        'cantidades_despachadas' => 'array',
        'fecha_confirmacion' => 'datetime',
        'fecha_entrega_confirmada' => 'date',
    ];

    public const ESTADOS_ORDEN = [
        'Pendiente',
        'En Revisión',
        'Ajustado por Logística',
        'Aprobado',
        'Picking', // Even if picking is not a true DB state initially, sometimes companies use it. The prompt suggests Picking is a state. "si el pedido está en 'Picking'".
        'Despachado',
        'Entregado',
        'Anulado',
        'Cancelado por el cliente'
    ];

    public static function indiceEstado($estado)
    {
        return array_search($estado, self::ESTADOS_ORDEN);
    }


    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Acceso a través de relación: Pedido -> Cotizacion -> Cliente
     */
    public function cliente()
    {
        return $this->cotizacion->cliente ?? null;
    }

    /**
     * Relación con el vendedor (Usuario que generó el pedido)
     */
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con los detalles del pedido
     */
    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    public function getSubtotalAttribute()
    {
        return $this->getCalculatedTotals()['subtotal'];
    }

    public function getIgvAttribute()
    {
        return $this->getCalculatedTotals()['igv'];
    }

    public function getTotalAttribute()
    {
        return $this->getCalculatedTotals()['total'];
    }

    private function getCalculatedTotals()
    {
        $sumatoriaTotal = 0;
        $items = $this->items;
        $despachos = $this->cantidades_despachadas ?? [];
        if (is_string($despachos)) {
            $despachos = json_decode($despachos, true);
        }
        
        $plantilla = $this->cotizacion->plantilla ?? null;
        $nombrePlantilla = $plantilla->nombre ?? 'Universal';

        if ($items && $items->isNotEmpty()) {
            foreach ($items as $item) {
                $precioTotalFila = (float)$item->precio_total;

                if (is_array($despachos) && array_key_exists($item->id, $despachos)) {
                    $nuevaCantidad = (float) $despachos[$item->id];
                    $campos = is_string($item->campos_json) ? json_decode($item->campos_json, true) : $item->campos_json;

                    if (in_array($nombrePlantilla, ['Tratadas', 'Pets'])) {
                        $cantMillar = (float) ($campos['cantidad_millar'] ?? 0);
                        $totalDerivado = $nuevaCantidad * $cantMillar;
                        $precioTotalFila = $totalDerivado * (float)$item->precio_unitario;
                    } elseif ($nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                        $originalFardos = (float) ($campos['cantidad_fardos'] ?? 1);
                        if ($originalFardos <= 0) $originalFardos = 1;
                        $originalKilos = (float) ($campos['total_kilos'] ?? 0);
                        $pesoPromedio = $originalKilos / $originalFardos;
                        
                        $totalDerivado = $nuevaCantidad * $pesoPromedio;
                        $precioTotalFila = $totalDerivado * (float)$item->precio_unitario;
                    } elseif ($nombrePlantilla === 'Bolsas de Polipropileno') {
                        $originalFardos = (float) ($campos['fardo'] ?? 1);
                        if ($originalFardos <= 0) $originalFardos = 1;
                        $originalKilos = (float) ($campos['total_kilos'] ?? 0);
                        $pesoPromedio = $originalKilos / $originalFardos;
                        
                        $totalDerivado = $nuevaCantidad * $pesoPromedio;
                        $precioTotalFila = $totalDerivado * (float)$item->precio_unitario;
                    } elseif ($nombrePlantilla === 'Universal') {
                        $precioTotalFila = $nuevaCantidad * (float)$item->precio_unitario;
                    }
                }
                $sumatoriaTotal += $precioTotalFila;
            }
        }

        $subtotal = $sumatoriaTotal / 1.18;
        $igv = $sumatoriaTotal - $subtotal;

        return [
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $sumatoriaTotal
        ];
    }
}
