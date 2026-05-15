<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    protected $table = 'crm_competencia';

    protected $fillable = [
        'cliente_id',
        'producto_id',
        'proveedor_nombre',
        'precio_ofrecido',
        'motivo_perdida',
        'entrega_proveedor',
        'entrega_nuestra',
        'detalle_perdida',
        'unidad_volumen',
        'fecha_dato',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
