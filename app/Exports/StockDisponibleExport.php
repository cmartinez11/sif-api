<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockDisponibleExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * Define header row.
     */
    public function headings(): array
    {
        return [
            'CÓDIGO',
            'PRODUCTO',
            'LÍNEA',
            'U/M',
            'STOCK FÍSICO DB',
            'COMPROMETIDO FUTURO',
            'VENTAS HOY DESPACHO FUTURO',
            'SALDO DISPONIBLE SIF'
        ];
    }

    /**
     * Map each row.
     */
    public function map($row): array
    {
        $stockActual = (float)$row->stock;
        $deudaArrastrada = (float)($row->deuda_arrastrada ?? 0.0);
        $comprometido = (float)($row->stock_comprometido ?? 0.0);
        $vendidoHoyFuturo = (float)($row->vendido_hoy_futuro ?? 0.0);
        $saldoSif = ($stockActual - $deudaArrastrada) - $comprometido + $vendidoHoyFuturo;

        return [
            $row->codigo,
            $row->nombre,
            $row->linea ?? 'N/A',
            $row->unidad_medida_logistica ?? 'N/A',
            number_format($stockActual, 3, '.', ''),
            number_format($comprometido, 3, '.', ''),
            number_format($vendidoHoyFuturo, 3, '.', ''),
            number_format($saldoSif, 3, '.', '')
        ];
    }
}
