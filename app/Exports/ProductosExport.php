<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductosExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Producto::all();
    }

    /**
     * Mapear las columnas que se van a exportar por cada fila
     */
    public function map($producto): array
    {
        return [
            $producto->codigo,
            $producto->nombre,
            $producto->unidad_medida,
            $producto->precio_base,
            $producto->unidad_medida_logistica,
            $producto->linea,
            $producto->sublinea,
            $producto->estado ? 'Activo' : 'Inactivo',
            $producto->peso,
            $producto->stock,
        ];
    }

    /**
     * Definir las cabeceras del archivo Excel
     */
    public function headings(): array
    {
        return [
            'codigo',
            'nombre_producto',
            'unidad_medida',
            'precio_base',
            'unidad_medida_logistica',
            'linea',
            'sublinea',
            'estado',
            'peso',
            'stock',
        ];
    }
}
