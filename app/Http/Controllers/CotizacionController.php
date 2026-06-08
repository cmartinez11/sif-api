<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Models\Plantilla;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Contacto;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateExport;
use Illuminate\Support\Str;

class CotizacionController extends Controller
{
    /**
     * Verifica que el vendedor acceda solo a sus cotizaciones
     * Dispara 403 Forbidden si no es propietario
     */
    private function authorizeVendedor(Cotizacion $cotizacion)
    {
        if (auth()->user()->hasRole('Vendedor') && $cotizacion->vendedor_id !== auth()->id()) {
            abort(403, 'No tienes permiso para acceder a esta cotización.');
        }
    }

    public function index()
    {
        // Vendedores see only theirs; Supervisors see all.
        $query = Cotizacion::with(['cliente', 'plantilla'])->orderBy('id', 'desc');
        if (auth()->user()->hasRole('Vendedor')) {
            $query->where('vendedor_id', auth()->id());
        }
        $cotizaciones = $query->get();
        return view('cotizaciones.index', compact('cotizaciones'));
    }

    public function create(Request $request)
    {
        if (!$request->has('plantilla_id')) {
            $plantillas = Plantilla::all();
            return view('cotizaciones.select_plantilla', compact('plantillas'));
        }
        
        $plantilla = Plantilla::findOrFail($request->plantilla_id);
        $contactos = Contacto::orderBy('nombre')->get();
        $clientes = Cliente::with('contacto')->orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();
        $vendedoresCampo = User::role('Vendedor de Campo')->get();
        
        return view('cotizaciones.create', compact('plantilla', 'contactos', 'clientes', 'productos', 'vendedoresCampo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'plantilla_id' => 'required|exists:plantillas,id',
            'moneda' => 'required|in:soles,dolares',
            'tipo_cambio' => 'nullable|numeric',
            'condicion_pago_cotizacion' => 'required|string|in:CONTADO,7 DIAS,10 DIAS,15 DIAS,20 DIAS,30 DIAS,45 DIAS,60 DIAS,90 DIAS',
            'itemsJson' => 'required',
            'fecha_entrega_estimada' => 'nullable|date',
            'vendedor_campo_id' => 'nullable|exists:users,id',
        ]);

        $cotizacion = new Cotizacion();
        // Generate number (example logic)
        $lastQuote = Cotizacion::orderBy('id', 'desc')->first();
        $nextId = $lastQuote ? $lastQuote->id + 1 : 1;
        $cotizacion->numero = str_pad($nextId, 12, '0', STR_PAD_LEFT);

        $cotizacion->vendedor_id = auth()->id();
        $cotizacion->cliente_id = $request->cliente_id;
        $cotizacion->plantilla_id = $request->plantilla_id;
        $cotizacion->condicion_pago = $request->input('condicion_pago_cotizacion');

        $cotizacion->agencia = $request->agencia;
        $cotizacion->direccion_agencia = $request->direccion_agencia;
        $cotizacion->observaciones = $request->observaciones;
        $cotizacion->moneda = $request->moneda;
        $cotizacion->tipo_cambio = $request->tipo_cambio;
        $cotizacion->fecha_emision = date('Y-m-d');
        $cotizacion->fecha_entrega_estimada = $request->fecha_entrega_estimada;
        $cotizacion->vendedor_campo_id = $request->vendedor_campo_id;
        $cotizacion->subtotal = $request->subtotal;
        $cotizacion->igv = $request->igv;
        $cotizacion->total = $request->total_final;
        $cotizacion->estado = 'Borrador';
        $cotizacion->save();

        $items = json_decode($request->itemsJson, true);
        $hasInsufficientStock = false;
        foreach($items as $i) {
            if(!empty($i['producto_id'])) {
                $cotizacionItem = new CotizacionItem();
                $cotizacionItem->cotizacion_id = $cotizacion->id;
                $cotizacionItem->producto_id = $i['producto_id'];
                $cotizacionItem->campos_json = json_encode($i);
                $cotizacionItem->precio_unitario = $i['precio_unitario'] ?? 0;
                $cotizacionItem->precio_total = $i['precio_total'] ?? 0;
                $cotizacionItem->estado_item = $i['estado_item'] ?? 'Activo';
                $cotizacionItem->motivo_rechazo = $i['motivo_rechazo'] ?? null;
                $cotizacionItem->precio_competencia = !empty($i['precio_competencia']) ? $i['precio_competencia'] : null;
                $cotizacionItem->save();

                if (($i['estado_item'] ?? 'Activo') === 'Activo') {
                    $producto = Producto::find($i['producto_id']);
                    if ($producto) {
                        $nombrePlantilla = $cotizacion->plantilla ? $cotizacion->plantilla->nombre : '';
                        $qty = 0;
                        if ($nombrePlantilla === 'Universal') {
                            $qty = (float)($i['cantidad'] ?? 0);
                        } elseif ($nombrePlantilla === 'Bolsas de Polipropileno' || $nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                            $qty = (float)($i['total_kilos'] ?? 0);
                        } else {
                            $qty = (float)($i['total_millares'] ?? 0);
                        }

                        if ($qty > (float)$producto->saldo_disponible_sif) {
                            $hasInsufficientStock = true;
                        }
                    }
                }
            }
        }

        if ($hasInsufficientStock) {
            return redirect()->route('cotizaciones.index')
                ->with('warning', 'Cotización registrada exitosamente. Nota: Algunos productos presentan stock insuficiente y se ha generado una alerta de producción.');
        }

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización generada exitosamente.');
    }

    public function show(Cotizacion $cotizacione)
    {
        $this->authorizeVendedor($cotizacione);
        $cotizacione->load(['cliente', 'plantilla', 'items.producto']);
        return view('cotizaciones.show', ['cotizacion' => $cotizacione]);
    }

    public function edit(Cotizacion $cotizacione)
    {
        $this->authorizeVendedor($cotizacione);

        if ($cotizacione->estado !== 'Borrador') {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'No se puede editar una cotización que ya ha sido cerrada o anulada.');
        }
        $cotizacione->load(['cliente.contacto', 'plantilla', 'items.producto']);
        $plantilla = $cotizacione->plantilla;
        $contactos = Contacto::orderBy('nombre')->get();
        $clientes = Cliente::with('contacto')->orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();
        $vendedoresCampo = User::role('Vendedor de Campo')->get();
        
        return view('cotizaciones.edit', compact('cotizacione', 'plantilla', 'contactos', 'clientes', 'productos', 'vendedoresCampo'));
    }

    public function update(Request $request, Cotizacion $cotizacione)
    {
        $this->authorizeVendedor($cotizacione);

        if ($cotizacione->estado !== 'Borrador') {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'No se puede actualizar una cotización que ya ha sido cerrada o anulada.');
        }

        $request->validate([
            'cliente_id'                => 'required|exists:clientes,id',
            'moneda'                    => 'required|in:soles,dolares',
            'tipo_cambio'               => 'nullable|numeric',
            'condicion_pago_cotizacion' => 'required|string|in:CONTADO,7 DIAS,10 DIAS,15 DIAS,20 DIAS,30 DIAS,45 DIAS,60 DIAS,90 DIAS',
            'itemsJson'                 => 'required',
            'fecha_entrega_estimada'    => 'nullable|date',
            'vendedor_campo_id'         => 'nullable|exists:users,id',
        ]);

        // Actualizar cabecera de la cotización.
        // Las cotizaciones son propuestas comerciales: NO modifican el stock de productos.
        $cotizacione->cliente_id             = $request->cliente_id;
        $cotizacione->condicion_pago         = $request->input('condicion_pago_cotizacion');
        $cotizacione->agencia                = $request->agencia;
        $cotizacione->direccion_agencia      = $request->direccion_agencia;
        $cotizacione->observaciones          = $request->observaciones;
        $cotizacione->moneda                 = $request->moneda;
        $cotizacione->tipo_cambio            = $request->tipo_cambio;
        $cotizacione->fecha_entrega_estimada = $request->fecha_entrega_estimada;
        $cotizacione->vendedor_campo_id      = $request->vendedor_campo_id;
        $cotizacione->subtotal               = $request->subtotal;
        $cotizacione->igv                    = $request->igv;
        $cotizacione->total                  = $request->total_final;
        $cotizacione->save();

        // Regenerar ítems: borrar los anteriores y re-insertar los nuevos del formulario.
        // El inventario (productos.stock) NO se toca aquí.
        $cotizacione->items()->delete();
        $items = json_decode($request->itemsJson, true);
        $hasInsufficientStock = false;

        foreach ($items as $i) {
            if (empty($i['producto_id'])) {
                continue;
            }

            $cotizacionItem                     = new CotizacionItem();
            $cotizacionItem->cotizacion_id      = $cotizacione->id;
            $cotizacionItem->producto_id        = $i['producto_id'];
            $cotizacionItem->campos_json        = json_encode($i);
            $cotizacionItem->precio_unitario    = $i['precio_unitario']    ?? 0;
            $cotizacionItem->precio_total       = $i['precio_total']       ?? 0;
            $cotizacionItem->estado_item        = $i['estado_item']        ?? 'Activo';
            $cotizacionItem->motivo_rechazo     = $i['motivo_rechazo']     ?? null;
            $cotizacionItem->precio_competencia = !empty($i['precio_competencia'])
                ? $i['precio_competencia']
                : null;
            $cotizacionItem->save();

            if (($i['estado_item'] ?? 'Activo') === 'Activo') {
                $producto = Producto::find($i['producto_id']);
                if ($producto) {
                    $nombrePlantilla = $cotizacione->plantilla ? $cotizacione->plantilla->nombre : '';
                    $qty = 0;
                    if ($nombrePlantilla === 'Universal') {
                        $qty = (float)($i['cantidad'] ?? 0);
                    } elseif ($nombrePlantilla === 'Bolsas de Polipropileno' || $nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                        $qty = (float)($i['total_kilos'] ?? 0);
                    } else {
                        $qty = (float)($i['total_millares'] ?? 0);
                    }

                    if ($qty > (float)$producto->saldo_disponible_sif) {
                        $hasInsufficientStock = true;
                    }
                }
            }

            // Registrar competencia si el ítem fue rechazado
            $pData = $i['perdida_data'] ?? null;
            if ($cotizacionItem->estado_item === 'Rechazado'
                && (!empty($cotizacionItem->motivo_rechazo) || $pData)) {
                \App\Models\Competencia::updateOrCreate(
                    [
                        'cliente_id'       => $cotizacione->cliente_id,
                        'producto_id'      => $cotizacionItem->producto_id,
                        'proveedor_nombre' => $pData['proveedor_nombre']
                            ?? $cotizacionItem->motivo_rechazo
                            ?? 'No especificado',
                    ],
                    [
                        'precio_ofrecido'   => (!empty($pData['precio_ofrecido'])
                            ? $pData['precio_ofrecido']
                            : ($cotizacionItem->precio_competencia ?? 0)) ?: 0,
                        'motivo_perdida'    => $pData['motivo_perdida']    ?? '',
                        'entrega_proveedor' => $pData['entrega_proveedor'] ?? '',
                        'entrega_nuestra'   => $pData['entrega_nuestra']   ?? '',
                        'detalle_perdida'   => $pData['detalle_perdida']   ?? '',
                        'fecha_dato'        => date('Y-m-d'),
                    ]
                );
            }
        }

        if ($hasInsufficientStock) {
            return redirect()->route('cotizaciones.index')
                ->with('warning', 'Cotización registrada exitosamente. Nota: Algunos productos presentan stock insuficiente y se ha generado una alerta de producción.');
        }

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización actualizada exitosamente.');
    }

    public function destroy(Cotizacion $cotizacione)
    {
        $this->authorizeVendedor($cotizacione);
        $cotizacione->items()->delete();
        $cotizacione->delete();
        return redirect()->route('cotizaciones.index')->with('success', 'Cotización eliminada exitosamente.');
    }

    public function generatePdf(Cotizacion $cotizacion)
    {
        $this->authorizeVendedor($cotizacion);
        
        // Cargamos las relaciones explícitamente y filtramos ítems activos
        $cotizacion->load([
            'cliente', 
            'plantilla', 
            'items' => function ($query) {
                $query->where('estado_item', 'Activo')->with('producto');
            }, 
            'vendedor'
        ]);
        
        // VALIDACIÓN CRÍTICA: Si la plantilla es null
        if (!$cotizacion->plantilla) {
            return "Error: La cotización #{$cotizacion->numero} no tiene una plantilla asignada.";
        }

        $nombreVista = Str::slug($cotizacion->plantilla->nombre);
        
        // --- NUEVA LÓGICA DE LOGO EN BASE64 ---
        $logoBase64 = null;
        $path = public_path('logo2.png');
        
        if (file_exists($path)) {
            // Leemos el archivo y lo convertimos a texto Base64
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        // ---------------------------------------

        try {
            $pdf = Pdf::loadView('pdf.cotizacion-' . $nombreVista, [
                'cotizacion' => $cotizacion,
                'logoBase64' => $logoBase64 // Pasamos la nueva variable
            ]);
            
            return $pdf->stream('Cotizacion-'.$cotizacion->numero.'.pdf');
        } catch (\Exception $e) {
            // Si falla, nos dirá exactamente por qué
            return "Error al generar el PDF: " . $e->getMessage();
        }
    }

    public function descargarJpg(Cotizacion $cotizacion)
    {
        $this->authorizeVendedor($cotizacion);
        
        // Cargamos las relaciones explícitamente y filtramos ítems activos
        $cotizacion->load([
            'cliente', 
            'plantilla', 
            'items' => function ($query) {
                $query->where('estado_item', 'Activo')->with('producto');
            }, 
            'vendedor'
        ]);
        
        // VALIDACIÓN CRÍTICA: Si la plantilla es null
        if (!$cotizacion->plantilla) {
            return "Error: La cotización #{$cotizacion->numero} no tiene una plantilla asignada.";
        }

        $nombreVista = Str::slug($cotizacion->plantilla->nombre);
        
        // --- NUEVA LÓGICA DE LOGO EN BASE64 ---
        $logoBase64 = null;
        $path = public_path('logo2.png');
        
        if (file_exists($path)) {
            // Leemos el archivo y lo convertimos a texto Base64
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        // ---------------------------------------

        return view('pdf.cotizacion-' . $nombreVista, [
            'cotizacion' => $cotizacion,
            'logoBase64' => $logoBase64,
            'isJpg' => true
        ]);
    }

    public function downloadTemplateTratadas()
    {
        return Excel::download(new TemplateExport(['codigo_producto', 'cantidad_por_millar', 'fardo', 'precio_unitario']), 'plantilla-tratadas.xlsx');
    }

    public function importTratadas(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $data = Excel::toArray([], $request->file('file'));
            $rows = $data[0] ?? [];

            if (count($rows) <= 1) {
                return response()->json(['success' => false, 'message' => 'El archivo está vacío o no tiene el formato correcto.']);
            }

            // Eliminar fila de cabecera
            array_shift($rows);

            $items = [];
            $missing = [];
            $count = 0;

            foreach ($rows as $row) {
                $codigo = trim($row[0] ?? '');
                $cantidad_millar = (float)($row[1] ?? 0);
                $fardo = (float)($row[2] ?? 0);
                $precio_unitario = (float)($row[3] ?? 0);

                if (empty($codigo)) continue;

                $producto = Producto::where('codigo', $codigo)->first();

                if ($producto) {
                    $total_millares = $cantidad_millar * $fardo;
                    $items[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'cantidad_millar' => $cantidad_millar,
                        'fardo' => $fardo,
                        'total_millares' => $total_millares,
                        'precio_unitario' => $precio_unitario,
                        'precio_total' => $total_millares * $precio_unitario,
                    ];
                    $count++;
                } else {
                    $missing[] = $codigo;
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'count' => $count,
                'missing' => $missing
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplateUniversal()
    {
        return Excel::download(new TemplateExport(['codigo_producto', 'cantidad', 'precio_unitario']), 'plantilla-universal.xlsx');
    }

    public function importUniversal(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $data = Excel::toArray([], $request->file('file'));
            $rows = $data[0] ?? [];

            if (count($rows) <= 1) {
                return response()->json(['success' => false, 'message' => 'El archivo está vacío o no tiene el formato correcto.']);
            }

            // Eliminar fila de cabecera
            array_shift($rows);

            $items = [];
            $missing = [];
            $count = 0;

            foreach ($rows as $row) {
                $codigo = trim($row[0] ?? '');
                $cantidad = (float)($row[1] ?? 0);
                $precio_unitario = (float)($row[2] ?? 0);

                if (empty($codigo)) continue;

                $producto = Producto::where('codigo', $codigo)->first();

                if ($producto) {
                    $items[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'cantidad' => $cantidad,
                        'unidad' => $producto->unidad_medida,
                        'precio_unitario' => $precio_unitario,
                        'precio_total' => $cantidad * $precio_unitario,
                    ];
                    $count++;
                } else {
                    $missing[] = $codigo;
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'count' => $count,
                'missing' => $missing
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplatePps()
    {
        return Excel::download(new TemplateExport(['codigo_producto', 'cantidad', 'fardo', 'precio_unitario']), 'plantilla-pps.xlsx');
    }

    public function importPps(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $data = Excel::toArray([], $request->file('file'));
            $rows = $data[0] ?? [];

            if (count($rows) <= 1) {
                return response()->json(['success' => false, 'message' => 'El archivo está vacío o no tiene el formato correcto.']);
            }

            // Eliminar fila de cabecera
            array_shift($rows);

            $items = [];
            $missing = [];
            $count = 0;

            foreach ($rows as $row) {
                $codigo = trim($row[0] ?? '');
                $cantidad = (float)($row[1] ?? 0);
                $fardo = (float)($row[2] ?? 0);
                $precio_unitario = (float)($row[3] ?? 0);

                if (empty($codigo)) continue;

                $producto = Producto::where('codigo', $codigo)->first();

                if ($producto) {
                    $total_kilos = $cantidad * $fardo;
                    $items[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'cantidad' => $cantidad,
                        'fardo' => $fardo,
                        'total_kilos' => $total_kilos,
                        'precio_unitario' => $precio_unitario,
                        'precio_total' => $total_kilos * $precio_unitario,
                    ];
                    $count++;
                } else {
                    $missing[] = $codigo;
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'count' => $count,
                'missing' => $missing
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplatePets()
    {
        return Excel::download(new TemplateExport(['codigo_producto', 'cantidad_millar', 'cant_sac_caj_bol', 'precio_unitario']), 'plantilla-pets.xlsx');
    }

    public function importPets(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $data = Excel::toArray([], $request->file('file'));
            $rows = $data[0] ?? [];

            if (count($rows) <= 1) {
                return response()->json(['success' => false, 'message' => 'El archivo está vacío o no tiene el formato correcto.']);
            }

            // Eliminar fila de cabecera
            array_shift($rows);

            $items = [];
            $missing = [];
            $count = 0;

            foreach ($rows as $row) {
                $codigo = trim($row[0] ?? '');
                $cantidad_millar = (float)($row[1] ?? 0);
                $cant_sac_caj_bol = (float)($row[2] ?? 0);
                $precio_unitario = (float)($row[3] ?? 0);

                if (empty($codigo)) continue;

                $producto = Producto::where('codigo', $codigo)->first();

                if ($producto) {
                    $total_millares = $cantidad_millar * $cant_sac_caj_bol;
                    $items[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'cantidad_millar' => $cantidad_millar,
                        'cant_sac_caj_bol' => $cant_sac_caj_bol,
                        'total_millares' => $total_millares,
                        'precio_unitario' => $precio_unitario,
                        'precio_total' => $total_millares * $precio_unitario,
                    ];
                    $count++;
                } else {
                    $missing[] = $codigo;
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'count' => $count,
                'missing' => $missing
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplatePolipropilenoKilos()
    {
        return Excel::download(new TemplateExport(['codigo_producto', 'cantidad_fardos', 'total_kilos', 'precio_unitario']), 'plantilla-polipropileno-kilos.xlsx');
    }

    public function importPolipropilenoKilos(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $data = Excel::toArray([], $request->file('file'));
            $rows = $data[0] ?? [];

            if (count($rows) <= 1) {
                return response()->json(['success' => false, 'message' => 'El archivo está vacío o no tiene el formato correcto.']);
            }

            // Eliminar fila de cabecera
            array_shift($rows);

            $items = [];
            $missing = [];
            $count = 0;

            foreach ($rows as $row) {
                $codigo = trim($row[0] ?? '');
                $cantidad_fardos = (float)($row[1] ?? 0);
                $total_kilos = (float)($row[2] ?? 0);
                $precio_unitario = (float)($row[3] ?? 0);

                if (empty($codigo)) continue;

                $producto = Producto::where('codigo', $codigo)->first();

                if ($producto) {
                    $items[] = [
                        'producto_id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'unidad' => $producto->unidad_medida,
                        'cantidad_fardos' => $cantidad_fardos,
                        'total_kilos' => $total_kilos,
                        'precio_unitario' => $precio_unitario,
                        'precio_total' => $total_kilos * $precio_unitario,
                    ];
                    $count++;
                } else {
                    $missing[] = $codigo;
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'count' => $count,
                'missing' => $missing
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function anular(Cotizacion $cotizacion)
    {
        $this->authorizeVendedor($cotizacion);

        if ($cotizacion->estado !== 'Borrador') {
            return back()->with('error', 'Solo se pueden anular cotizaciones en estado Borrador.');
        }

        $cotizacion->estado = 'Anulado';
        $cotizacion->save();

        // Anular pedidos vinculados si existen
        $pedido = \App\Models\Pedido::where('cotizacion_id', $cotizacion->id)->first();
        if ($pedido) {
            $pedido->estado = 'Anulado';
            $pedido->save();
        }

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización anulada correctamente.');
    }

    public function duplicar($id)
    {
        if (!auth()->user()->hasRole('Vendedor')) {
            abort(403, 'No tienes permiso para duplicar cotizaciones.');
        }

        $cotizacion = Cotizacion::with('items')->findOrFail($id);
        
        $this->authorizeVendedor($cotizacion);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $nuevaCotizacion = $cotizacion->replicate();
            $nuevaCotizacion->estado = 'Borrador';
            
            $lastQuote = Cotizacion::orderBy('id', 'desc')->first();
            $nextId = $lastQuote ? $lastQuote->id + 1 : 1;
            $nuevaCotizacion->numero = str_pad($nextId, 12, '0', STR_PAD_LEFT);
            $nuevaCotizacion->fecha_emision = now()->format('Y-m-d');
            $nuevaCotizacion->save();

            foreach ($cotizacion->items as $item) {
                $nuevoItem = $item->replicate();
                $nuevoItem->cotizacion_id = $nuevaCotizacion->id;
                $nuevoItem->save();
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('cotizaciones.index')->with('success', 'Cotización duplicada correctamente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('cotizaciones.index')->with('error', 'Ocurrió un error al duplicar la cotización: ' . $e->getMessage());
        }
    }
}
