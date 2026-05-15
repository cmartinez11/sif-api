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
     * Acceso a través de relación: Pedido -> Cotizacion -> Vendedor
     */
    public function vendedor()
    {
        return $this->cotizacion->vendedor ?? null;
    }

    /**
     * Acceso a items calculados con los ajustes de logística
     */
    public function getItemsAttribute()
    {
        // Solo obtener ítems que no hayan sido rechazados en la cotización
        $items = $this->cotizacion->items()->where('estado_item', '!=', 'Rechazado')->get();
        $despachos = $this->cantidades_despachadas ?? [];
        $nombrePlantilla = $this->cotizacion->plantilla->nombre;

        foreach ($items as $item) {
            if (is_array($despachos) && array_key_exists($item->id, $despachos)) {
                $nuevaCantidad = (float) $despachos[$item->id];
                $campos = json_decode($item->campos_json, true);

                // Actualizar cantidad base en el JSON según la plantilla
                if (in_array($nombrePlantilla, ['Tratadas', 'Bolsas de Polipropileno', 'Pets'])) {
                    $campos['fardo'] = $nuevaCantidad;
                } elseif ($nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                    $campos['cantidad_fardos'] = $nuevaCantidad;
                } else {
                    $campos['cantidad'] = $nuevaCantidad;
                }

                // Recalcular totales del ítem
                if (in_array($nombrePlantilla, ['Tratadas', 'Pets'])) {
                    $cantMillar = (float) ($campos['cantidad_millar'] ?? 0);
                    $campos['total_millares'] = $nuevaCantidad * $cantMillar;
                    $item->precio_total = $campos['total_millares'] * (float)$item->precio_unitario;
                } elseif ($nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                    // Calculamos peso promedio basado en el original
                    $originalFardos = (float) (json_decode($item->getOriginal('campos_json'), true)['cantidad_fardos'] ?? 1);
                    if ($originalFardos <= 0) $originalFardos = 1;
                    $originalKilos = (float) (json_decode($item->getOriginal('campos_json'), true)['total_kilos'] ?? 0);
                    $pesoPromedio = $originalKilos / $originalFardos;
                    
                    $campos['total_kilos'] = $nuevaCantidad * $pesoPromedio;
                    $item->precio_total = $campos['total_kilos'] * (float)$item->precio_unitario;
                } elseif ($nombrePlantilla === 'Bolsas de Polipropileno') {
                    // Similar a por kilos pero el campo es total_kilos directamente
                    $originalFardos = (float) (json_decode($item->getOriginal('campos_json'), true)['fardo'] ?? 1);
                    if ($originalFardos <= 0) $originalFardos = 1;
                    $originalKilos = (float) (json_decode($item->getOriginal('campos_json'), true)['total_kilos'] ?? 0);
                    $pesoPromedio = $originalKilos / $originalFardos;
                    
                    $campos['total_kilos'] = $nuevaCantidad * $pesoPromedio;
                    $item->precio_total = $campos['total_kilos'] * (float)$item->precio_unitario;
                } elseif ($nombrePlantilla === 'Universal') {
                    $item->precio_total = $nuevaCantidad * (float)$item->precio_unitario;
                }

                $item->campos_json = json_encode($campos);
            }
        }

        return $items;
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
        foreach ($this->items as $item) {
            $sumatoriaTotal += $item->precio_total;
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
