<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->hasRole('Logistico')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $query = Contacto::withCount('clientes');
        $contactos = $query->orderBy('nombre')->paginate(20);
        return view('contactos.index', compact('contactos'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $query = Contacto::withCount('clientes');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('telefono', 'ILIKE', "%{$search}%")
                  ->orWhereHas('clientes', function($q2) use ($search) {
                      $q2->where('nombre', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $contactos = $query->orderBy('nombre')->get();

        return view('contactos._table_rows', compact('contactos'))->render();
    }

    public function create()
    {
        return view('contactos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
        ]);

        Contacto::create($validated);
        return redirect()->route('contactos.index')->with('success', 'Contacto creado exitosamente.');
    }

    public function edit(Contacto $contacto)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        return view('contactos.edit', compact('contacto', 'clientes'));
    }

    public function update(Request $request, Contacto $contacto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:255',
        ]);

        $contacto->update($validated);

        // Update associated clients
        // The request should contain an array of client_ids
        $clientIds = $request->input('client_ids', []);
        
        // 1. Unset this contact from clients NOT in the list but currently associated with it
        Cliente::where('contacto_id', $contacto->id)
            ->whereNotIn('id', $clientIds)
            ->update(['contacto_id' => null]);

        // 2. Set this contact for clients in the list
        if (!empty($clientIds)) {
            Cliente::whereIn('id', $clientIds)
                ->update(['contacto_id' => $contacto->id]);
        }

        return redirect()->route('contactos.index')->with('success', 'Contacto actualizado exitosamente.');
    }

    public function destroy(Contacto $contacto)
    {
        $contacto->delete();
        return redirect()->route('contactos.index')->with('success', 'Contacto eliminado exitosamente.');
    }
}
