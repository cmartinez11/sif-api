<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ClientesImport;
use App\Imports\ProductosImport;
use App\Imports\ContactosImport;
use App\Exports\TemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Administrador']);
    }

    public function index()
    {
        return view('importacion.index');
    }

    public function downloadTemplate(string $type)
    {
        $templates = [
            'clientes' => ['ruc', 'razon_social', 'direccion', 'departamento', 'provincia', 'distrito', 'condicion_pago', 'nombre_contacto', 'enlace_contacto'],
            'productos' => ['codigo', 'nombre_producto', 'unidad_medida', 'precio_base', 'unidad_medida_logistica', 'linea', 'sublinea', 'estado', 'peso', 'stock'],
            'contactos' => ['nombre_completo', 'telefono', 'correo_electronico'],
        ];

        if (!array_key_exists($type, $templates)) {
            abort(404);
        }

        return Excel::download(new TemplateExport($templates[$type]), "plantilla-{$type}.xlsx");
    }

    public function importClientes(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new ClientesImport();
        Excel::import($import, $request->file('file'));

        $failures = collect($import->failures())->map(function ($failure) {
            return 'Fila ' . $failure->row() . ': ' . implode('; ', $failure->errors());
        })->all();

        return back()->with('success', "Clientes importados: {$import->imported}")->with('failures', $failures);
    }

    public function importProductos(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new ProductosImport();
        Excel::import($import, $request->file('file'));

        $failures = collect($import->failures())->map(function ($failure) {
            return 'Fila ' . $failure->row() . ': ' . implode('; ', $failure->errors());
        })->all();

        return back()->with('success', "Productos importados: {$import->imported}")->with('failures', $failures);
    }

    public function importContactos(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new ContactosImport();
        Excel::import($import, $request->file('file'));

        $failures = collect($import->failures())->map(function ($failure) {
            return 'Fila ' . $failure->row() . ': ' . implode('; ', $failure->errors());
        })->all();

        return back()->with('success', "Contactos importados: {$import->imported}")->with('failures', $failures);
    }
}
