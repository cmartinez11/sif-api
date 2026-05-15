<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\PerfilCliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('ruc', 'ILIKE', "%{$search}%")
                  ->orWhere('nombre', 'ILIKE', "%{$search}%");
            });
        }

        $clientes = $query->orderBy('nombre')->paginate(20)->withQueryString();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        if (auth()->user()->hasRole('Logistico')) abort(403);
        $contactos = Contacto::orderBy('nombre')->get();
        return view('clientes.create', compact('contactos'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('Logistico')) abort(403);
        
        $validated = $request->validate([
            // Datos del Cliente
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|max:11|unique:clientes',
            'direccion' => 'nullable|string|max:255',
            'condicion_pago' => 'required|string|in:CONTADO,7 DIAS,10 DIAS,15 DIAS,20 DIAS,30 DIAS,45 DIAS,60 DIAS,90 DIAS',
            'provincia' => 'nullable|string|max:255',
            'distrito' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'contacto_id' => 'nullable|exists:contactos,id',
            // Perfil Técnico CRM
            'tipo_preforma' => 'nullable|string|max:255',
            'gramaje' => 'nullable|string|max:255',
            'cuello' => 'nullable|string|max:255',
            'aplicacion' => 'nullable|string|max:255',
            'cant_maquinas' => 'nullable|numeric',
            'vol_mensual' => 'nullable|numeric',
            'frecuencia_compra' => 'nullable|string|max:255',
            'proveedor_actual' => 'nullable|string|max:255',
            'problemas_proveedor' => 'nullable|string|max:255',
            'urgencias_frecuentes' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ]);

        // 1. Crear el cliente base
        $cliente = Cliente::create([
            'nombre' => $validated['nombre'],
            'ruc' => $validated['ruc'],
            'direccion' => $validated['direccion'],
            'condicion_pago' => $validated['condicion_pago'],
            'provincia' => $validated['provincia'],
            'distrito' => $validated['distrito'],
            'departamento' => $validated['departamento'],
            'contacto_id' => $validated['contacto_id'],
        ]);

        // 2. Usar el ID para crear su perfil CRM
        PerfilCliente::create([
            'cliente_id' => $cliente->id,
            'tipo_preforma' => $validated['tipo_preforma'] ?? null,
            'gramaje' => $validated['gramaje'] ?? null,
            'cuello' => $validated['cuello'] ?? null,
            'aplicacion' => $validated['aplicacion'] ?? null,
            'cant_maquinas' => $validated['cant_maquinas'] ?? 0,
            'vol_mensual' => $validated['vol_mensual'] ?? 0,
            'frecuencia_compra' => $validated['frecuencia_compra'] ?? null,
            'proveedor_actual' => $validated['proveedor_actual'] ?? null,
            'problemas_proveedor' => $validated['problemas_proveedor'] ?? null,
            'urgencias_frecuentes' => $validated['urgencias_frecuentes'] ?? false,
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente y su Perfil Técnico CRM creados exitosamente.');
    }

    public function edit(Cliente $cliente)
    {
        $contactos = Contacto::orderBy('nombre')->get();
        return view('clientes.edit', compact('cliente', 'contactos'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            // Datos del Cliente
            'nombre' => 'required|string|max:255',
            'ruc' => 'required|string|max:11|unique:clientes,ruc,' . $cliente->id,
            'direccion' => 'nullable|string|max:255',
            'condicion_pago' => 'required|string|in:CONTADO,7 DIAS,10 DIAS,15 DIAS,20 DIAS,30 DIAS,45 DIAS,60 DIAS,90 DIAS',
            'provincia' => 'nullable|string|max:255',
            'distrito' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'contacto_id' => 'nullable|exists:contactos,id',
            // Perfil Técnico CRM
            'tipo_preforma' => 'nullable|string|max:255',
            'gramaje' => 'nullable|string|max:255',
            'cuello' => 'nullable|string|max:255',
            'aplicacion' => 'nullable|string|max:255',
            'cant_maquinas' => 'nullable|numeric',
            'vol_mensual' => 'nullable|numeric',
            'frecuencia_compra' => 'nullable|string|max:255',
            'proveedor_actual' => 'nullable|string|max:255',
            'problemas_proveedor' => 'nullable|string|max:255',
            'urgencias_frecuentes' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ]);

        $cliente->update([
            'nombre' => $validated['nombre'],
            'ruc' => $validated['ruc'],
            'direccion' => $validated['direccion'],
            'condicion_pago' => $validated['condicion_pago'],
            'provincia' => $validated['provincia'],
            'distrito' => $validated['distrito'],
            'departamento' => $validated['departamento'],
            'contacto_id' => $validated['contacto_id'],
        ]);

        // Guardar/Actualizar Perfil CRM usando updateOrCreate
        PerfilCliente::updateOrCreate(
            ['cliente_id' => $cliente->id],
            [
                'tipo_preforma' => $validated['tipo_preforma'] ?? null,
                'gramaje' => $validated['gramaje'] ?? null,
                'cuello' => $validated['cuello'] ?? null,
                'aplicacion' => $validated['aplicacion'] ?? null,
                'cant_maquinas' => $validated['cant_maquinas'] ?? 0,
                'vol_mensual' => $validated['vol_mensual'] ?? 0,
                'frecuencia_compra' => $validated['frecuencia_compra'] ?? null,
                'proveedor_actual' => $validated['proveedor_actual'] ?? null,
                'problemas_proveedor' => $validated['problemas_proveedor'] ?? null,
                'urgencias_frecuentes' => $validated['urgencias_frecuentes'] ?? false,
                'observaciones' => $validated['observaciones'] ?? null,
            ]
        );

        return redirect()->route('clientes.index')->with('success', 'Cliente y su Perfil CRM actualizados exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }

}
