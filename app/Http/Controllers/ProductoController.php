<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{

    public function __construct()
    {
        // Esta línea protege el sistema: 
        // Solo Admin y Supervisor pueden crear, editar o borrar.
        // Los vendedores solo pueden ver (index y show).
        $this->middleware('role:Administrador|Supervisor')->except(['index','show']);
    }

    public function index()
    {
        $productos = Producto::all();
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        $lineas = [
            'BOBINA AD', 'BOBINA BD', 'BOBINA PP', 
            'BOLSAS AD', 'BOLSAS BD', 'BOLSAS PP', 
            'PET', 'TERMOFORMADO PP'
        ];
        $sublineas = [
            'ASA', 'BANDEJA', 'BOLSA PEAD CUADRADA', 'BOLSA PEAD ROLLOS', 
            'BOLSA PEAD TRATADA', 'BOLSA PEAD T-SHIRT', 'CUCHARA', 
            'PET', 'PLATO', 'RESPOSTERO', 'SALCHIFENIX', 
            'TAPA', 'TENEDOR'
        ];
        return view('productos.create', compact('lineas','sublineas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:255|unique:productos',
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'nullable|string|max:255',
            'precio_base' => 'required|numeric|min:0',
            'linea' => 'required|string|max:255',
            'sublinea' => 'nullable|string|max:255',
            'estado' => 'required|boolean',
            'peso' => 'nullable|numeric|min:0',
            'unidad_medida_logistica' => 'nullable|string|max:255',
        ]);

        // Evitar el error 500 de Postgres por la columna 'stock' nula
        $validated['stock'] = 0; 

        Producto::create($validated);
        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        $lineas = [
            'BOBINA AD', 'BOBINA BD', 'BOBINA PP', 
            'BOLSAS AD', 'BOLSAS BD', 'BOLSAS PP', 
            'PET', 'TERMOFORMADO PP'
        ];
        $sublineas = [
            'ASA', 'BANDEJA', 'BOLSA PEAD CUADRADA', 'BOLSA PEAD ROLLOS', 
            'BOLSA PEAD TRATADA', 'BOLSA PEAD T-SHIRT', 'CUCHARA', 
            'PET', 'PLATO', 'RESPOSTERO', 'SALCHIFENIX', 
            'TAPA', 'TENEDOR'
        ];
        return view('productos.edit', compact('producto', 'lineas', 'sublineas'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            // 'codigo' no se valida ni actualiza por seguridad
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'nullable|string|max:255',
            'precio_base' => 'required|numeric|min:0',
            'linea' => 'required|string|max:255',
            'sublinea' => 'nullable|string|max:255',
            'estado' => 'required|boolean',
            'peso' => 'nullable|numeric|min:0',
            'unidad_medida_logistica' => 'nullable|string|max:255',
        ]);

        $producto->update($validated);
        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
