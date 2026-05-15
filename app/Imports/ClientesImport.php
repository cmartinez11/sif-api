<?php

namespace App\Imports;

use App\Models\Cliente;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class ClientesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public $imported = 0;

    public function model(array $row)
    {
        $this->imported++;

        // 1. Lógica del Contacto (Si viene en el Excel)
        $contactoId = null;
        if (!empty($row['nombre_contacto'])) {
            // Buscamos o creamos el contacto por nombre (mapeado a 'nombre' en DB)
            $contacto = \App\Models\Contacto::firstOrCreate(
                ['nombre' => trim($row['nombre_contacto'])]
            );

            // Si trae enlace, lo actualizamos
            if (!empty($row['enlace_contacto'])) {
                $contacto->enlace = $row['enlace_contacto'];
                $contacto->save();
            }
            $contactoId = $contacto->id;
        }

        // 2. Crear o actualizar el Cliente usando updateOrCreate por el campo ruc
        return \App\Models\Cliente::updateOrCreate(
            ['ruc' => trim((string)$row['ruc'])],
            [
                'nombre'         => $row['nombre_social'] ?? $row['nombre'] ?? $row['razon_social'],
                'direccion'      => $row['direccion'] ?? null,
                'departamento'   => $row['departamento'] ?? null,
                'provincia'      => $row['provincia'] ?? null,
                'distrito'       => $row['distrito'] ?? null,
                'condicion_pago' => $row['condicion_pago'] ?? 'CONTADO',
                'contacto_id'    => $contactoId,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'ruc'              => 'required|string|max:255',
            'nombre_social'    => 'nullable|string|max:255',
            'nombre'           => 'nullable|string|max:255',
            'razon_social'     => 'nullable|string|max:255',
            'direccion'        => 'nullable|string|max:255',
            'departamento'     => 'nullable|string|max:255',
            'provincia'        => 'nullable|string|max:255',
            'distrito'         => 'nullable|string|max:255',
            'condicion_pago'   => 'required|string|in:CONTADO,7 DIAS,10 DIAS,15 DIAS,20 DIAS,30 DIAS,45 DIAS,60 DIAS,90 DIAS',
            'nombre_contacto'  => 'nullable|string|max:255',
            'enlace_contacto'  => 'nullable|string|max:500',
        ];
    }
}

