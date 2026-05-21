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
        ]);

        $plantilla = $pedido->loadMissing('cotizacion.plantilla')->cotizacion->plantilla->nombre;

        $despachosAnteriores = is_string($pedido->cantidades_despachadas) 
            ? json_decode($pedido->cantidades_despachadas, true) 
            : ($pedido->cantidades_despachadas ?? []);

        // 1. VALIDACIÓN ESTRICTA (Bloqueo de digitación)
        foreach ($pedido->items as $item) {
            $cantidadBase = 0;
            if (!empty($despachosAnteriores)) {
                $cantidadBase = floatval($despachosAnteriores[$item->id] ?? 0);
            } else {
                $campos = json_decode($item->campos_json, true);
                if (in_array($plantilla, ['Tratadas', 'Bolsas de Polipropileno', 'Pets'])) {
                    $cantidadBase = floatval($campos['fardo'] ?? 0);
                } elseif ($plantilla === 'Bolsas de Polipropileno por kilos') {
                    $cantidadBase = floatval($campos['cantidad_fardos'] ?? 0);
                } else {
                    $cantidadBase = floatval($campos['cantidad'] ?? 0);
                }
            }

            $cantidadIngresada = isset($request->items[$item->id]['cantidad']) 
                                ? floatval($request->items[$item->id]['cantidad']) 
                                : $cantidadBase;

            if ($cantidadIngresada > $cantidadBase) {
                return redirect()->back()->with('error', "Error de digitación: No puedes despachar {$cantidadIngresada} unidades del producto '{$item->producto->nombre}'. El máximo permitido para este despacho es {$cantidadBase}.")->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $despachoActual = [];
            $saldosPendientes = [];
            $generarBackorder = false;
            $sumaSaldos = 0;

            // 1. Lectura Dinámica del JSON y Cálculo de Saldos
            foreach ($pedido->items as $item) {
                $cantidadBase = 0;
                if (!empty($despachosAnteriores)) {
                    $cantidadBase = floatval($despachosAnteriores[$item->id] ?? 0);
                } else {
                    $campos = json_decode($item->campos_json, true);
                    if (in_array($plantilla, ['Tratadas', 'Bolsas de Polipropileno', 'Pets'])) {
                        $cantidadBase = floatval($campos['fardo'] ?? 0);
                    } elseif ($plantilla === 'Bolsas de Polipropileno por kilos') {
                        $cantidadBase = floatval($campos['cantidad_fardos'] ?? 0);
                    } else {
                        $cantidadBase = floatval($campos['cantidad'] ?? 0);
                    }
                }

                $cantidadAjustada = floatval($request->items[$item->id]['cantidad'] ?? $cantidadBase);
                $saldo = $cantidadBase - $cantidadAjustada;

                $despachoActual[$item->id] = $cantidadAjustada;

                if ($saldo > 0) {
                    $saldosPendientes[$item->id] = $saldo;
                    $sumaSaldos += $saldo;
                }
            }

            if ($sumaSaldos > 0) {
                $generarBackorder = true;
            }

            // 3. Actualización del Pedido Original
            $pedido->estado = 'Ajustado por Logística';
            $pedido->cantidades_despachadas = json_encode($despachoActual);
            $pedido->save();

            // 4. Generación del Nuevo Pedido (Backorder)
            if ($generarBackorder) {
                $baseNumeroPed = explode('-', $pedido->numero)[0];
                
                // Cuenta cuántos pedidos existen en la BD que empiecen con esa base usando LIKE
                $conteo = \App\Models\Pedido::where('numero', 'LIKE', "{$baseNumeroPed}-%")->count();
                
                // Genera el nuevo número sumando 1 al conteo y formateando con ceros
                $nuevoNumeroCorrelativo = $baseNumeroPed . '-' . str_pad($conteo + 1, 2, '0', STR_PAD_LEFT);
                
                // Crea el nuevo pedido
                $nuevoPedido = $pedido->replicate();
                $nuevoPedido->numero = $nuevoNumeroCorrelativo;
                $nuevoPedido->estado = 'Pendiente';
                $nuevoPedido->save();

                // Replicar los detalles del pedido para el Backorder y mapear IDs
                $idMap = [];
                foreach ($pedido->items as $item) {
                    $nuevoItem = $item->replicate();
                    $nuevoItem->pedido_id = $nuevoPedido->id;
                    $nuevoItem->save();
                    $idMap[$item->id] = $nuevoItem->id;
                }

                // Ajustar cantidades_despachadas utilizando los nuevos IDs del Backorder
                $nuevoSaldos = [];
                foreach ($saldosPendientes as $oldId => $saldo) {
                    if (isset($idMap[$oldId])) {
                        $nuevoSaldos[$idMap[$oldId]] = $saldo;
                    }
                }

                $nuevoPedido->cantidades_despachadas = json_encode($nuevoSaldos);
                $nuevoPedido->save();
            }

            DB::commit();
            return redirect()->route('pedidos.show', $pedido)->with('success', 'Ajuste confirmado y Backorder generado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
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

        // Eager loading
        $pedido->load(['cotizacion.cliente', 'items.producto', 'cotizacion.plantilla']);

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

}
