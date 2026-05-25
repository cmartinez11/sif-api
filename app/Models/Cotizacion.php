<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;
use App\Models\Plantilla;
use App\Models\CotizacionItem;
use App\Models\User;

class Cotizacion extends Model
{
    use HasFactory;
    protected $table = 'cotizaciones';

    protected $fillable = [
        'numero',
        'vendedor_id',
        'cliente_id',
        'plantilla_id',
        'moneda',
        'estado',
        'fecha_emision',
        'fecha_entrega_estimada',
        'vendedor_campo_id',
        'agencia',
        'direccion_agencia',
        'observaciones',
        'subtotal',
        'igv',
        'total',
    ];

    protected $casts = [
        'fecha_entrega_estimada' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function vendedorCampo()
    {
        return $this->belongsTo(User::class, 'vendedor_campo_id');
    }

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id');
    }

    public function items()
    {
        return $this->hasMany(CotizacionItem::class, 'cotizacion_id');
    }

    /**
     * Relación inversa: Una cotización puede tener muchos pedidos
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cotizacion_id');
    }
}
