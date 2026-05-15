<?php

namespace App\Imports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class ProductosImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public $imported = 0;

    public function model(array $row)
    {
        // Añadir tiempo extra para cada bloque procesado
        ini_set('max_execution_time', '300');

        $this->imported++;
        
        Producto::updateOrCreate(
            ['codigo' => trim((string)($row['codigo'] ?? ''))], // Criterio ÚNICO de búsqueda
            [
                'nombre'                  => trim((string)($row['nombre_producto'] ?? '')),
                'unidad_medida'           => trim((string)($row['unidad_medida'] ?? '')) ?: null,
                'precio_base'             => (float) ($row['precio_base'] ?? 0),
                'unidad_medida_logistica' => trim((string)($row['unidad_medida_logistica'] ?? '')) ?: null,
                'linea'                   => trim((string)($row['linea'] ?? '')),
                'sublinea'                => trim((string)($row['sublinea'] ?? '')) ?: null,
                'estado'                  => in_array(trim((string)($row['estado'] ?? '')), ['1', 'Activo', 'true', 'activo']) ? 1 : 0,
                'peso'                    => (float) ($row['peso'] ?? 0),
                'stock'                   => (int) ($row['stock'] ?? 0),
            ]
        );

        return null; // Retornamos null para que la librería no intente un INSERT redundante
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:255',
            'nombre_producto' => 'required|string|max:255',
            'unidad_medida' => 'nullable|string|max:255',
            'precio_base' => 'nullable', // Validación numérica se evita por el filtrado de limpieza general del import
            'unidad_medida_logistica' => 'nullable|string|max:255',
            'linea' => 'required|string|max:255',
            'sublinea' => 'nullable|string|max:255',
            'estado' => 'required',
            'peso' => 'nullable', // Igual que el precio base, sanitizado a float directamente
            'stock' => 'nullable|numeric',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
