<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'productos';
    protected $guarded = [];

    protected $cast = [
        'stock' => 'decimal:3',
        'precio_base' => 'decimal:2',
        'peso' => 'decimal:3',
        'estado' => 'boolean',
    ];

    /**
     * Relación: Un producto puede tener muchos items de cotización
     */
    public function cotizacionItems()
    {
        return $this->hasMany(CotizacionItem::class);
    }
}
