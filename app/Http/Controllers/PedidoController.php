<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        // Obtener la consulta base con sus relaciones
        $query = Pedido::with(['cotizacion.cliente', 'cotizacion.plantilla', 'vendedor']);

        // Seguridad: Si es vendedor, solo ve los suyos
        if (auth()->user()->hasRole('Vendedor')) {
            $query->where('user_id', auth()->id());
        }

        // 1. Filtro: Rango de Fecha de Pedido (created_at)
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio . ' 00:00:00', 
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        // 2. Filtro: Fecha de Despacho (fecha_entrega_confirmada)
        if ($request->filled('fecha_despacho')) {
            $query->whereDate('fecha_entrega_confirmada', $request->fecha_despacho);
        }

        // 3. Filtro: Estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // 4. Filtro: Vendedor
        if ($request->filled('vendedor_id')) {
            $query->where('user_id', $request->vendedor_id);
        }

        // Ordenar por defecto y obtener resultados
        $pedidos = $query->orderBy('created_at', 'desc')->get();

        // Obtener lista de vendedores para el filtro
        $vendedores = \App\Models\User::role('Vendedor')->get();

        return view('pedidos.index', compact('pedidos', 'vendedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cotizacion_id' => 'required|exists:cotizaciones,id',
        ]);

        $cotizacion = Cotizacion::findOrFail($request->cotizacion_id);

        if ($cotizacion->estado == 'Convertida a Pedido') {
            return back()->with('error', 'Esta cotización ya fue convertida a pedido.');
        }

        try {
            DB::beginTransaction();

            $pedido = new Pedido();
            
            // Generar correlativo independiente (P00000001)
            // Buscamos el último pedido "Principal" (excluyendo backorders con guion)
            $ultimoPedidoPrincipal = Pedido::where('numero', 'NOT LIKE', '%-%')
                                           ->latest('id')
                                           ->first();

            if ($ultimoPedidoPrincipal) {
                // Extraer la parte numérica (quitando la 'P'), sumar 1 y formatear
                $numeroEntero = (int) str_replace('P', '', $ultimoPedidoPrincipal->numero);
                $siguienteNumero = $numeroEntero + 1;
            } else {
                // Si es el primer pedido del sistema
                $siguienteNumero = 1;
            }

            $pedido->numero = 'P' . str_pad($siguienteNumero, 8, '0', STR_PAD_LEFT);
            
            $pedido->cotizacion_id = $cotizacion->id;
            $pedido->user_id = $cotizacion->vendedor_id;
            $pedido->estado = 'Pendiente';
            $pedido->fecha_pedido = date('Y-m-d');
            $pedido->fecha_confirmacion = now();
            $pedido->save();

            // Copiar los ítems activos de la cotización al detalle del pedido
            $items = $cotizacion->items()->where('estado_item', '!=', 'Rechazado')->get();
            foreach ($items as $item) {
                $pedidoItem = new \App\Models\PedidoItem();
                $pedidoItem->pedido_id = $pedido->id;
                $pedidoItem->producto_id = $item->producto_id;
                $pedidoItem->unidad_medida = $item->producto->unidad_medida ?? 'Und';
                $pedidoItem->precio_unitario = $item->precio_unitario;
                $pedidoItem->precio_total = $item->precio_total;
                $pedidoItem->campos_json = $item->campos_json;
                $pedidoItem->save();

                // Buscar el producto correspondiente en la tabla 'productos' usando su ID.
                $producto = \App\Models\Producto::findOrFail($item->producto_id);

                // Obtener cantidad de forma precisa de campos_json
                $campos = json_decode($pedidoItem->campos_json, true) ?: [];
                $cantidad = 0.0;
                if (isset($campos['total_kilos']) && $campos['total_kilos'] !== '') {
                    $cantidad = (float) $campos['total_kilos'];
                } elseif (isset($campos['total_millares']) && $campos['total_millares'] !== '') {
                    $cantidad = (float) $campos['total_millares'];
                } elseif (isset($campos['cantidad']) && $campos['cantidad'] !== '') {
                    $cantidad = (float) $campos['cantidad'];
                } elseif (isset($campos['fardo']) && $campos['fardo'] !== '') {
                    $cantidad = (float) $campos['fardo'];
                } elseif (isset($campos['cantidad_fardos']) && $campos['cantidad_fardos'] !== '') {
                    $cantidad = (float) $campos['cantidad_fardos'];
                } elseif (isset($campos['cantidad_millar']) && $campos['cantidad_millar'] !== '') {
                    $cantidad = (float) $campos['cantidad_millar'];
                }

                // Restar la cantidad solicitada (decimales:3) directamente de la columna 'stock' casteando a float en PHP.
                $producto->stock = (float)$producto->stock - (float)$cantidad;
                $producto->save();
            }

            $cotizacion->estado = 'Convertida a Pedido';
            $cotizacion->save();

            DB::commit();

            return redirect()->route('pedidos.index')->with('success', 'Pedido confirmado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar el pedido: ' . $e->getMessage());
        }
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['cotizacion.cliente', 'items.producto', 'cotizacion.plantilla', 'vendedor']);
        return view('pedidos.show', compact('pedido'));
    }

    public function updateEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|string',
        ]);

        // Antes de guardar el nuevo estado, validar si ya está cancelado
        if ($pedido->estado === 'Cancelado por el cliente') {
            return back()->with('error', 'Acción denegada: Un pedido cancelado no puede cambiar de estado.');
        }

        $estadoActual = $pedido->estado;
        $nuevoEstado = $request->estado;

        // Validar que el nuevo estado sea válido
        if (!in_array($nuevoEstado, \App\Models\Pedido::ESTADOS_ORDEN)) {
            return back()->with('error', 'Estado no válido.');
        }

        $indiceActual = \App\Models\Pedido::indiceEstado($estadoActual);
        $indiceNuevo = \App\Models\Pedido::indiceEstado($nuevoEstado);

        // Permitir retroceso o salto solo si es a 'Anulado', pero en general prohibir retrocesos
        if ($nuevoEstado !== 'Anulado' && $indiceNuevo !== false && $indiceActual !== false) {
            if ($indiceNuevo < $indiceActual) {
                return back()->with('error', 'No se puede retroceder el estado de un pedido.');
            }
        }

        $pedido->estado = $nuevoEstado;
        $pedido->save();

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function ajustarCantidades(Request $request, Pedido $pedido)
    {
        if (!auth()->user()->hasAnyRole(['Logistico', 'Supervisor', 'Administrador'])) {
            return back()->with('error', 'No tienes permiso para ajustar cantidades.');
        }

        $request->validate([
            'items' => 'required|array',
            'fecha_entrega_confirmada' => 'required|date',
            'items.*.cantidad' => 'required|numeric|min:0',
        ]);

        // Resolviendo la plantilla de forma segura (admite pedido directo)
        $plantilla = 'Universal';
        if ($pedido->cotizacion_id && $pedido->cotizacion && $pedido->cotizacion->plantilla) {
            $plantilla = $pedido->cotizacion->plantilla->nombre;
        } else {
            $meta = is_string($pedido->cantidades_json) 
                ? json_decode($pedido->cantidades_json, true) 
                : ($pedido->cantidades_json ?? []);
            
            $tipoDirecto = $meta['tipo_directo'] ?? 'universal';
            if ($tipoDirecto === 'tratadas') {
                $plantilla = 'Tratadas';
            } elseif ($tipoDirecto === 'bolsas-polipropileno') {
                $plantilla = 'Bolsas de Polipropileno';
            } elseif ($tipoDirecto === 'pets') {
                $plantilla = 'Pets';
            } elseif ($tipoDirecto === 'bolsas-polipropileno-kilos') {
                $plantilla = 'Bolsas de Polipropileno por kilos';
            }
        }

        try {
            DB::beginTransaction();

            $nuevoPedidoSaldo = null;
            $saldosDespachosJson = [];

            foreach ($pedido->items as $item) {
                if (!isset($request->items[$item->id])) {
                    continue;
                }

                $campos = json_decode($item->campos_json, true) ?: [];

                // 1. Obtener la cantidad física original restada del stock
                $originalPhysicalQty = 0.0;
                if (isset($campos['total_kilos']) && $campos['total_kilos'] !== '') {
                    $originalPhysicalQty = (float)$campos['total_kilos'];
                } elseif (isset($campos['total_millares']) && $campos['total_millares'] !== '') {
                    $originalPhysicalQty = (float)$campos['total_millares'];
                } elseif (isset($campos['cantidad']) && $campos['cantidad'] !== '') {
                    $originalPhysicalQty = (float)$campos['cantidad'];
                } elseif (isset($campos['fardo']) && $campos['fardo'] !== '') {
                    $originalPhysicalQty = (float)$campos['fardo'];
                } elseif (isset($campos['cantidad_fardos']) && $campos['cantidad_fardos'] !== '') {
                    $originalPhysicalQty = (float)$campos['cantidad_fardos'];
                } elseif (isset($campos['cantidad_millar']) && $campos['cantidad_millar'] !== '') {
                    $originalPhysicalQty = (float)$campos['cantidad_millar'];
                }

                // Obtener cantidad original visual
                $originalQty = 0.0;
                if (in_array($plantilla, ['Tratadas', 'Bolsas de Polipropileno', 'Pets'])) {
                    $originalQty = (float) ($campos['fardo'] ?? 0);
                } elseif ($plantilla === 'Bolsas de Polipropileno por kilos') {
                    $originalQty = (float) ($campos['cantidad_fardos'] ?? 0);
                } else {
                    $originalQty = (float) ($campos['cantidad'] ?? 0);
                }

                // 2. Obtener la nueva cantidad enviada por Logística
                $newQtyLogistica = (float)$request->items[$item->id]['cantidad'];

                // 3. Calcular la nueva cantidad física y actualizar el JSON
                $newPhysicalQty = 0.0;
                $pesoPromedio = 1.0;
                $cantidadMillar = 1.0;

                if (in_array($plantilla, ['Tratadas', 'Pets'])) {
                    $cantidadMillar = (float)($campos['cantidad_millar'] ?? 0.0);
                    $newTotalMillares = $newQtyLogistica * $cantidadMillar;
                    $newPhysicalQty = $newTotalMillares;

                    $campos['fardo'] = $newQtyLogistica;
                    $campos['total_millares'] = $newTotalMillares;
                } elseif ($plantilla === 'Bolsas de Polipropileno') {
                    $originalFardos = (float)($campos['fardo'] ?? 1.0);
                    if ($originalFardos <= 0) {
                        $originalFardos = 1.0;
                    }
                    $originalKilos = (float)($campos['total_kilos'] ?? 0.0);
                    $pesoPromedio = $originalKilos / $originalFardos;
                    
                    $newTotalKilos = $newQtyLogistica * $pesoPromedio;
                    $newPhysicalQty = $newTotalKilos;

                    $campos['fardo'] = $newQtyLogistica;
                    $campos['total_kilos'] = $newTotalKilos;
                } elseif ($plantilla === 'Bolsas de Polipropileno por kilos') {
                    $originalFardos = (float)($campos['cantidad_fardos'] ?? 1.0);
                    if ($originalFardos <= 0) {
                        $originalFardos = 1.0;
                    }
                    $originalKilos = (float)($campos['total_kilos'] ?? 0.0);
                    $pesoPromedio = $originalKilos / $originalFardos;

                    $newTotalKilos = $newQtyLogistica * $pesoPromedio;
                    $newPhysicalQty = $newTotalKilos;

                    $campos['cantidad_fardos'] = $newQtyLogistica;
                    $campos['total_kilos'] = $newTotalKilos;
                } else {
                    $newPhysicalQty = $newQtyLogistica;
                    $campos['cantidad'] = $newQtyLogistica;
                }

                // 4. Comparar y actualizar stock físico
                $diferencia = $newPhysicalQty - $originalPhysicalQty;

                $producto = \App\Models\Producto::findOrFail($item->producto_id);
                // Si Logística aumentó la cantidad, resta de stock; si disminuyó, devuelve a stock
                $producto->stock = (float)round((float)$producto->stock - $diferencia, 3);

                // 5. Recalcular total del ítem y guardar campos_json
                $item->precio_total = (float)($newPhysicalQty * (float)$item->precio_unitario);
                $campos['precio_total'] = $item->precio_total;
                $item->campos_json = json_encode($campos);
                $item->save();

                // 6. Si la cantidad ingresada es menor, registrar saldo pendiente y comprometer stock
                if ($newQtyLogistica < $originalQty) {
                    $saldoQty = $originalQty - $newQtyLogistica;

                    // Crear el pedido de saldo (Backorder) bajo demanda en el primer saldo
                    if (!$nuevoPedidoSaldo) {
                        $baseNumeroPed = explode('-', $pedido->numero)[0];
                        
                        // Cuenta cuántos pedidos existen en la BD que empiecen con esa base usando LIKE
                        $conteo = Pedido::where('numero', 'LIKE', "{$baseNumeroPed}-%")->count();
                        
                        // Genera el nuevo número sumando 1 al conteo y formateando con ceros
                        $nuevoNumeroCorrelativo = $baseNumeroPed . '-' . str_pad($conteo + 1, 2, '0', STR_PAD_LEFT);
                        
                        // Crear cabecera
                        $nuevoPedidoSaldo = Pedido::create([
                            'numero' => $nuevoNumeroCorrelativo,
                            'cotizacion_id' => $pedido->cotizacion_id,
                            'user_id' => $pedido->user_id,
                            'estado' => 'Pendiente',
                            'fecha_pedido' => $pedido->fecha_pedido,
                            'fecha_confirmacion' => now(),
                            'fecha_entrega_confirmada' => null,
                            'cantidades_json' => $pedido->cantidades_json,
                            'cantidades_despachadas' => null,
                        ]);
                    }

                    // Calcular cantidad física del saldo
                    $physicalSaldo = 0.0;
                    $camposNuevos = json_decode($item->campos_json, true) ?: [];

                    if (in_array($plantilla, ['Tratadas', 'Pets'])) {
                        $camposNuevos['fardo'] = $saldoQty;
                        $camposNuevos['total_millares'] = $saldoQty * $cantidadMillar;
                        $physicalSaldo = $camposNuevos['total_millares'];
                    } elseif (in_array($plantilla, ['Bolsas de Polipropileno', 'Bolsas de Polipropileno por kilos'])) {
                        if ($plantilla === 'Bolsas de Polipropileno') {
                            $camposNuevos['fardo'] = $saldoQty;
                        } else {
                            $camposNuevos['cantidad_fardos'] = $saldoQty;
                        }
                        $camposNuevos['total_kilos'] = $saldoQty * $pesoPromedio;
                        $physicalSaldo = $camposNuevos['total_kilos'];
                    } else {
                        $camposNuevos['cantidad'] = $saldoQty;
                        $physicalSaldo = $saldoQty;
                    }

                    $precioTotalSaldoItem = (float)($physicalSaldo * (float)$item->precio_unitario);
                    $camposNuevos['precio_total'] = $precioTotalSaldoItem;

                    // Insertar físicamente en la tabla 'pedido_items'
                    $nuevoItemId = DB::table('pedido_items')->insertGetId([
                        'pedido_id' => $nuevoPedidoSaldo->id,
                        'producto_id' => $item->producto_id,
                        'unidad_medida' => $item->unidad_medida ?? 'Und',
                        'precio_unitario' => $item->precio_unitario,
                        'precio_total' => $precioTotalSaldoItem,
                        'campos_json' => json_encode($camposNuevos),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $saldosDespachosJson[$nuevoItemId] = $saldoQty;

                    // Comprometer el stock del saldo
                    $producto->stock = (float)round((float)$producto->stock - $physicalSaldo, 3);
                }

                $producto->save();
            }

            // 7. Actualizar parámetros del pedido original y aprobar
            $pedido->fecha_entrega_confirmada = $request->fecha_entrega_confirmada;
            $pedido->estado = 'Aprobado';
            $pedido->cantidades_despachadas = null;
            $pedido->save();

            // 8. Guardar cantidades_despachadas en el backorder si se creó
            if ($nuevoPedidoSaldo) {
                $nuevoPedidoSaldo->cantidades_despachadas = $saldosDespachosJson;
                $nuevoPedidoSaldo->save();
            }

            DB::commit();
            
            $msg = $nuevoPedidoSaldo 
                ? 'Pedido confirmado, aprobado y Backorder ' . $nuevoPedidoSaldo->numero . ' generado por el saldo.'
                : 'Pedido confirmado y aprobado exitosamente.';
                
            return redirect()->route('pedidos.show', $pedido)->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar el pedido: ' . $e->getMessage())->withInput();
        }
    }

    public function aprobar(Pedido $pedido)
    {
        if (!auth()->user()->hasAnyRole(['Vendedor', 'Supervisor', 'Administrador'])) {
            return back()->with('error', 'No tienes permiso para aprobar el pedido.');
        }

        $pedido->estado = 'Aprobado';
        $pedido->save();

        return redirect()->route('pedidos.show', $pedido)->with('success', 'Pedido aprobado definitivamente.');
    }

    public function descargarPicking(Pedido $pedido)
    {
        // Seguridad: Solo Logístico, Admin o Supervisor
        if (!auth()->user()->hasAnyRole(['Logistico', 'Supervisor', 'Administrador'])) {
            abort(403, 'No tienes permiso para descargar la hoja de picking.');
        }

        // Regla de Negocio: Solo pedidos aprobados
        if ($pedido->estado !== 'Aprobado') {
            return back()->with('error', 'La hoja de picking solo puede generarse para pedidos aprobados.');
        }

        $esPedidoDirecto = is_null($pedido->cotizacion_id);

        if ($esPedidoDirecto) {
            $pedido->load(['items.producto']);
            $pedido->setRelation('cotizacion', $pedido->cotizacion);
        } else {
            // Eager loading
            $pedido->load(['cotizacion.cliente', 'items.producto', 'cotizacion.plantilla']);
        }

        // Lógica de Logo Base64
        $logoBase64 = null;
        $path = public_path('logo2.png');
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $plantillaNombre = $pedido->cotizacion->plantilla->nombre;
        $nombreVista = \Illuminate\Support\Str::slug($plantillaNombre);
        
        $vistaDestino = view()->exists("pdf.picking-{$nombreVista}") 
                        ? "pdf.picking-{$nombreVista}" 
                        : 'pdf.picking';

        $pdf = Pdf::loadView($vistaDestino, [
            'pedido' => $pedido,
            'logoBase64' => $logoBase64
        ]);

        return $pdf->download("picking-pedido-{$pedido->numero}.pdf");
    }

    public function descargarPdf(Pedido $pedido)
    {
        if (auth()->user()->hasRole('Vendedor') && $pedido->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para descargar este PDF.');
        }

        $esPedidoDirecto = is_null($pedido->cotizacion_id);

        if ($esPedidoDirecto) {
            $pedido->load(['items.producto', 'vendedor']);
            $pedido->setRelation('cotizacion', $pedido->cotizacion);
        } else {
            $pedido->load(['cotizacion.cliente', 'items.producto', 'cotizacion.plantilla', 'vendedor']);
        }

        $logoBase64 = null;
        $path = public_path('logo2.png');
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $plantillaNombre = $pedido->cotizacion->plantilla->nombre ?? 'Universal';
        $slug = \Illuminate\Support\Str::slug($plantillaNombre);

        $vistaDestino = "pedidos.pdf.pedido-{$slug}";
        if (!view()->exists($vistaDestino)) {
            $vistaDestino = 'pedidos.pdf.pedido-universal';
        }

        $pdf = Pdf::loadView($vistaDestino, [
            'pedido' => $pedido,
            'logoBase64' => $logoBase64,
            'esPedidoDirecto' => $esPedidoDirecto
        ]);

        return $pdf->download("pedido-{$pedido->numero}.pdf");
    }

    public function confirmarFecha(Request $request, Pedido $pedido)
    {
        $request->validate([
            'fecha_entrega_confirmada' => 'required|date',
        ]);

        $pedido->fecha_entrega_confirmada = $request->fecha_entrega_confirmada;
        $pedido->save();

        return back()->with('success', 'Fecha de entrega confirmada correctamente.');
    }

    public function cancelarBackorder(Request $request, Pedido $pedido)
    {
        // Validaciones de seguridad
        if (!str_contains($pedido->numero, '-') || $pedido->estado !== 'Pendiente') {
            return redirect()->back()->with('error', 'Este pedido no puede ser cancelado de esta forma.');
        }

        try {
            DB::beginTransaction();

            $pedido->estado = 'Cancelado por el cliente';
            $pedido->save();

            $despachos = is_string($pedido->cantidades_despachadas) 
                ? json_decode($pedido->cantidades_despachadas, true) 
                : ($pedido->cantidades_despachadas ?? []);

            // Registrar en CRM Competencia únicamente para los ítems de este backorder que quedaron pendientes
            foreach ($pedido->items as $item) {
                if (is_array($despachos) && array_key_exists($item->id, $despachos)) {
                    \App\Models\Competencia::create([
                        'cliente_id'        => $pedido->cotizacion->cliente_id,
                        'producto_id'       => $item->producto_id,
                        'proveedor_nombre'  => $request->proveedor_nombre ?? 'Desconocido',
                        'precio_ofrecido'   => $request->precio_ofrecido ?? 0,
                        'motivo_perdida'    => $request->motivo_perdida,
                        'entrega_proveedor' => $request->entrega_proveedor,
                        'entrega_nuestra'   => $request->entrega_nuestra,
                        'detalle_perdida'   => $request->detalle_perdida,
                        'fecha_dato'        => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'El backorder ha sido cancelado y la pérdida registrada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al cancelar: ' . $e->getMessage());
        }
    }

    public function revertirACotizacion(Pedido $pedido)
    {
        // Seguridad: Solo Supervisor o Administrador
        if (!auth()->user()->hasAnyRole(['Supervisor', 'Administrador'])) {
            abort(403, 'No tienes permiso para revertir el pedido.');
        }

        // Validación: Solo si el pedido tiene el estado 'Pendiente'
        if ($pedido->estado !== 'Pendiente') {
            return back()->with('error', 'Acción denegada: Solo se pueden revertir pedidos con estado Pendiente.');
        }

        try {
            DB::beginTransaction();

            $cotizacion = $pedido->cotizacion;
            if ($cotizacion) {
                $cotizacion->estado = 'Borrador';
                $cotizacion->save();
            }

            $pedido->delete();

            DB::commit();
            return redirect()->route('pedidos.index')->with('success', 'El pedido ha sido revertido a cotización y eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al revertir el pedido: ' . $e->getMessage());
        }
    }

    public function crearDirecto($tipo)
    {
        $tiposValidos = ['tratadas', 'bolsas-polipropileno', 'pets', 'universal', 'bolsas-polipropileno-kilos'];
        if (!in_array($tipo, $tiposValidos)) {
            abort(404);
        }

        $plantillaNombre = 'Universal';
        if ($tipo === 'tratadas') {
            $plantillaNombre = 'Tratadas';
        } elseif ($tipo === 'bolsas-polipropileno') {
            $plantillaNombre = 'Bolsas de Polipropileno';
        } elseif ($tipo === 'pets') {
            $plantillaNombre = 'Pets';
        } elseif ($tipo === 'bolsas-polipropileno-kilos') {
            $plantillaNombre = 'Bolsas de Polipropileno por kilos';
        }

        $plantilla = \App\Models\Plantilla::where('nombre', $plantillaNombre)->first();
        if (!$plantilla) {
            $plantilla = \App\Models\Plantilla::create(['nombre' => $plantillaNombre]);
        }

        $contactos = \App\Models\Contacto::orderBy('nombre')->get();
        $clientes = \App\Models\Cliente::with('contacto')->orderBy('nombre')->get();
        $productos = \App\Models\Producto::orderBy('nombre')->get();
        $vendedoresCampo = \App\Models\User::role('Vendedor de Campo')->get();

        return view("pedidos.plantilla-{$tipo}.create", compact('plantilla', 'contactos', 'clientes', 'productos', 'vendedoresCampo', 'tipo'));
    }

    public function storeDirecto(Request $request, $tipo)
    {
        $tiposValidos = ['tratadas', 'bolsas-polipropileno', 'pets', 'universal', 'bolsas-polipropileno-kilos'];
        if (!in_array($tipo, $tiposValidos)) {
            abort(404);
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'moneda' => 'required|in:soles,dolares',
            'tipo_cambio' => 'nullable|numeric',
            'condicion_pago_cotizacion' => 'required|string|in:CONTADO,7 DIAS,10 DIAS,15 DIAS,20 DIAS,30 DIAS,45 DIAS,60 DIAS,90 DIAS',
            'itemsJson' => 'required',
            'fecha_entrega_estimada' => 'nullable|date',
            'vendedor_campo_id' => 'nullable|exists:users,id',
        ]);

        $items = json_decode($request->itemsJson, true);
        if (empty($items)) {
            return back()->with('error', 'El pedido debe tener al menos un producto seleccionado.')->withInput();
        }

        try {
            DB::beginTransaction();

            $pedido = new Pedido();

            // Generar correlativo independiente (PXXXXXXXX)
            $ultimoPedidoPrincipal = Pedido::where('numero', 'NOT LIKE', '%-%')
                                           ->latest('id')
                                           ->first();

            if ($ultimoPedidoPrincipal) {
                $numeroEntero = (int) str_replace('P', '', $ultimoPedidoPrincipal->numero);
                $siguienteNumero = $numeroEntero + 1;
            } else {
                $siguienteNumero = 1;
            }

            $pedido->numero = 'P' . str_pad($siguienteNumero, 8, '0', STR_PAD_LEFT);
            
            $pedido->cotizacion_id = null;
            $pedido->user_id = auth()->id();
            $pedido->estado = 'Pendiente';
            $pedido->fecha_pedido = date('Y-m-d');
            $pedido->fecha_confirmacion = now();

            $metadata = [
                'cliente_id' => $request->cliente_id,
                'tipo_directo' => $tipo,
                'moneda' => $request->moneda,
                'tipo_cambio' => (float)($request->tipo_cambio ?? 1.0),
                'condicion_pago' => $request->input('condicion_pago_cotizacion'),
                'agencia' => $request->agencia,
                'direccion_agencia' => $request->direccion_agencia,
                'observaciones' => $request->observaciones,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
                'vendedor_campo_id' => $request->vendedor_campo_id,
            ];
            
            $pedido->cantidades_json = $metadata;
            $pedido->save();

            foreach ($items as $i) {
                if (empty($i['producto_id'])) {
                    continue;
                }

                $pedidoItem = new \App\Models\PedidoItem();
                $pedidoItem->pedido_id = $pedido->id;
                $pedidoItem->producto_id = $i['producto_id'];
                
                $producto = \App\Models\Producto::findOrFail($i['producto_id']);
                $pedidoItem->unidad_medida = $producto->unidad_medida ?? 'Und';
                $pedidoItem->precio_unitario = (float)($i['precio_unitario'] ?? 0);
                $pedidoItem->precio_total = (float)($i['precio_total'] ?? 0);
                $pedidoItem->campos_json = json_encode($i);
                $pedidoItem->save();

                $cantidad = 0.0;
                if (isset($i['total_kilos']) && $i['total_kilos'] !== '') {
                    $cantidad = (float) $i['total_kilos'];
                } elseif (isset($i['total_millares']) && $i['total_millares'] !== '') {
                    $cantidad = (float) $i['total_millares'];
                } elseif (isset($i['cantidad']) && $i['cantidad'] !== '') {
                    $cantidad = (float) $i['cantidad'];
                } elseif (isset($i['fardo']) && $i['fardo'] !== '') {
                    $cantidad = (float) $i['fardo'];
                } elseif (isset($i['cantidad_fardos']) && $i['cantidad_fardos'] !== '') {
                    $cantidad = (float) $i['cantidad_fardos'];
                } elseif (isset($i['cantidad_millar']) && $i['cantidad_millar'] !== '') {
                    $cantidad = (float) $i['cantidad_millar'];
                }

                $nuevoStock = (float)$producto->stock - (float)$cantidad;
                if ($nuevoStock < 0.0) {
                    $nuevoStock = 0.0;
                }
                $producto->stock = $nuevoStock;
                $producto->save();
            }

            DB::commit();

            return redirect()->route('pedidos.index')->with('success', 'Pedido directo creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar el pedido directo: ' . $e->getMessage())->withInput();
        }
    }

}

