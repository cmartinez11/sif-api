<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_items';
    protected $guarded = [];

    protected $appends = [
        'cantidad_fardos_picking',
    ];

    /**
     * Accessor virtual para obtener la cantidad vendida desde campos_json.
     */
    public function getCantidadAttribute()
    {
        $campos = is_string($this->campos_json) ? json_decode($this->campos_json, true) : $this->campos_json;
        $campos = $campos ?: [];

        if (isset($campos['total_kilos']) && $campos['total_kilos'] !== '') {
            return (float) $campos['total_kilos'];
        }
        if (isset($campos['total_millares']) && $campos['total_millares'] !== '') {
            return (float) $campos['total_millares'];
        }
        if (isset($campos['cantidad']) && $campos['cantidad'] !== '') {
            return (float) $campos['cantidad'];
        }
        if (isset($campos['fardo']) && $campos['fardo'] !== '') {
            return (float) $campos['fardo'];
        }
        if (isset($campos['cantidad_fardos']) && $campos['cantidad_fardos'] !== '') {
            return (float) $campos['cantidad_fardos'];
        }
        if (isset($campos['cantidad_millar']) && $campos['cantidad_millar'] !== '') {
            return (float) $campos['cantidad_millar'];
        }

        return 0.0;
    }

    /**
     * Accessor virtual para obtener la cantidad convertida a fardos de picking.
     */
    public function getCantidadFardosPickingAttribute()
    {
        $unidadesPorFardo = $this->producto ? $this->producto->unidades_por_fardo : null;

        if (is_null($unidadesPorFardo) || (float)$unidadesPorFardo == 0) {
            return (float)$this->cantidad;
        }

        return (float)($this->cantidad / (float)$unidadesPorFardo);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
