<?php

namespace App\Imports;

use App\Models\Contacto;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class ContactosImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public $imported = 0;

    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $contactoData = [
            'nombre' => $data['nombre_completo'] ?? null, // Mapea nombre_completo a nombre
            'telefono' => $data['telefono'] ?? null,
            'correo' => $data['correo_electronico'] ?? null, // Mapea correo_electronico a correo
        ];

        if (!empty($contactoData['correo'])) {
            Contacto::updateOrCreate(
                ['correo' => $contactoData['correo']],
                $contactoData
            );
        } else {
            Contacto::create($contactoData);
        }

        $this->imported++;
    }

    public function rules(): array
    {
        return [
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'correo_electronico' => 'nullable|email|max:255',
        ];
    }
}
