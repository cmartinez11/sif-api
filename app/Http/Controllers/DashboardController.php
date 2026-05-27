<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. CONDICIONAL PARA EL ROL LOGÍSTICA
        if ($user->hasRole('Logistico')) {
            $hoy = Carbon::today();

            $despachosHoy = Pedido::whereDate('fecha_entrega_confirmada', $hoy)
                                              ->whereNotIn('estado', ['Entregado', 'Cancelado por el cliente'])
                                              ->count();

            $pendientesPicking = Pedido::where('estado', 'Aprobado')->count();

            $backordersEspera = Pedido::where('numero', 'LIKE', '%-%')
                                              ->where('estado', 'Pendiente')
                                              ->count();

            $entregadosSemana = Pedido::where('estado', 'Entregado')
                                              ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                                              ->count();

            $agendaDespachos = Pedido::with(['cotizacion.cliente', 'cotizacion.plantilla'])
                                             ->whereDate('fecha_entrega_confirmada', '>=', $hoy)
                                             ->whereNotIn('estado', ['Entregado', 'Cancelado por el cliente'])
                                             ->orderBy('fecha_entrega_confirmada', 'asc')
                                             ->take(10)
                                             ->get();

            // Pedidos en cola de producción (estado_produccion = POR PRODUCIR)
            $pedidosPorProducir = Pedido::where('estado_produccion', 'POR PRODUCIR')
                ->whereNotIn('estado', ['Anulado', 'Cancelado por el cliente'])
                ->count();

            // Alertas de ruptura (saldos negativos en stock en el SIF)
            $alertasRuptura = \App\Models\Producto::where('stock', '<', 0)
                ->orderBy('stock', 'asc') // Mayor deuda primero (más negativo)
                ->take(3)
                ->get();

            return view('dashboard.logistica', compact(
                'despachosHoy', 'pendientesPicking', 'backordersEspera', 'entregadosSemana', 'agendaDespachos', 'pedidosPorProducir', 'alertasRuptura'
            ));
        }

        // Rango de fechas para consultas y presets
        $preset = $request->input('date_preset', '');
        if (empty($preset)) {
            if ($request->has('start_date') || $request->has('end_date')) {
                $preset = 'personalizado';
            } else {
                $preset = 'este_mes'; // Default preset
            }
        }

        switch ($preset) {
            case 'hoy':
                $startDate = Carbon::today()->toDateString();
                $endDate = Carbon::today()->toDateString();
                break;
            case 'esta_semana':
                $startDate = Carbon::now()->startOfWeek()->toDateString();
                $endDate = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'este_mes':
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->endOfMonth()->toDateString();
                break;
            case 'anio_2026':
                $startDate = '2026-01-01';
                $endDate = '2026-12-31';
                break;
            case 'personalizado':
            default:
                $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
                $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
                break;
        }

        if ($user->hasAnyRole(['Supervisor', 'Administrador'])) {
            $vendedoras = User::role('Vendedor')->get();

            // Ranking vendedoras y total ventas periodo
            $pedidos = Pedido::with(['cotizacion', 'vendedor', 'items'])
                ->whereIn('estado', ['Aprobado', 'Despachado', 'Entregado'])
                ->whereBetween('fecha_pedido', [$startDate, $endDate])
                ->get();

            $rankingVendedoras = $pedidos->groupBy('user_id')->map(function ($group) {
                $first = $group->first();
                $vendedorName = $first->vendedor->name ?? 'N/A';
                $vendedorId = $first->user_id;

                $totalSoles = 0.0;
                $totalDolares = 0.0;
                $sortValue = 0.0;

                foreach ($group as $pedido) {
                    $totalOrder = (float)$pedido->total;
                    $moneda = $pedido->cotizacion ? $pedido->cotizacion->moneda : ($pedido->cantidades_json['moneda'] ?? 'soles');
                    $tipoCambio = $pedido->cotizacion ? (float)$pedido->cotizacion->tipo_cambio : (float)($pedido->cantidades_json['tipo_cambio'] ?? 1.0);

                    if ($moneda === 'dolares') {
                        $totalDolares += $totalOrder;
                        $sortValue += ($totalOrder * $tipoCambio);
                    } else {
                        $totalSoles += $totalOrder;
                        $sortValue += $totalOrder;
                    }
                }

                return [
                    'vendedor_id' => $vendedorId,
                    'vendedor_name' => $vendedorName,
                    'total_soles' => $totalSoles,
                    'total_dolares' => $totalDolares,
                    'sort_value' => $sortValue,
                    'cantidad_pedidos' => $group->count(),
                ];
            })->sortByDesc('sort_value')->values()->all();

            $totalVentasPeriodoSoles = collect($rankingVendedoras)->sum('total_soles');
            $totalVentasPeriodoDolares = collect($rankingVendedoras)->sum('total_dolares');

            // Eficiencia de conversión (cotizaciones convertidas vs emitidas en el periodo)
            $totalCotizaciones = Cotizacion::whereBetween('fecha_emision', [$startDate, $endDate])->count();
            $convertidas = Cotizacion::whereBetween('fecha_emision', [$startDate, $endDate])
                ->where('estado', 'Convertida a Pedido')
                ->count();
            $eficienciaConversion = $totalCotizaciones > 0 ? round(($convertidas / $totalCotizaciones) * 100, 2) : 0;

            // Pedidos en cola de producción (estado_produccion = POR PRODUCIR)
            $pedidosPorProducir = Pedido::where('estado_produccion', 'POR PRODUCIR')
                ->whereNotIn('estado', ['Anulado', 'Cancelado por el cliente'])
                ->count();

            // Alertas de ruptura (saldos negativos en stock en el SIF)
            $alertasRuptura = \App\Models\Producto::where('stock', '<', 0)
                ->orderBy('stock', 'asc') // Mayor deuda primero (más negativo)
                ->take(3)
                ->get();

            // -------------------------------------------------------------
            // ANALÍTICA COMERCIAL AVANZADA
            // -------------------------------------------------------------
            $items = \App\Models\PedidoItem::whereHas('pedido', function ($query) use ($startDate, $endDate) {
                $query->whereIn('estado', ['Aprobado', 'Despachado', 'Entregado'])
                      ->whereBetween('fecha_pedido', [$startDate, $endDate]);
            })->with(['producto', 'pedido.cotizacion'])->get();

            $salesByLine = [];
            $salesBySubline = [];
            $salesByProduct = [];

            foreach ($items as $item) {
                $producto = $item->producto;
                $linea = $producto ? ($producto->linea ?: 'SIN LINEA') : 'SIN LINEA';
                $sublinea = $producto ? ($producto->sublinea ?: 'SIN SUBLINEA') : 'SIN SUBLINEA';
                
                $precioFila = (float)$item->precio_total;
                $pedido = $item->pedido;
                $moneda = 'soles';
                $tipoCambio = 1.0;
                
                if ($pedido) {
                    $cot = $pedido->cotizacion;
                    if ($cot) {
                        $moneda = $cot->moneda ?? 'soles';
                        $tipoCambio = (float)($cot->tipo_cambio ?? 1.0);
                    }
                }
                
                $montoSoles = 0.0;
                $montoDolares = 0.0;
                
                if ($moneda === 'dolares') {
                    $montoDolares = $precioFila;
                    $montoSoles = $precioFila * $tipoCambio;
                } else {
                    $montoSoles = $precioFila;
                    $montoDolares = $tipoCambio > 0 ? ($precioFila / $tipoCambio) : 0.0;
                }
                
                // Group Line
                if (!isset($salesByLine[$linea])) {
                    $salesByLine[$linea] = [
                        'linea' => $linea,
                        'soles' => 0.0,
                        'dolares' => 0.0
                    ];
                }
                $salesByLine[$linea]['soles'] += $montoSoles;
                $salesByLine[$linea]['dolares'] += $montoDolares;
                
                // Group Subline
                if (!isset($salesBySubline[$linea])) {
                    $salesBySubline[$linea] = [];
                }
                if (!isset($salesBySubline[$linea][$sublinea])) {
                    $salesBySubline[$linea][$sublinea] = [
                        'sublinea' => $sublinea,
                        'soles' => 0.0,
                        'dolares' => 0.0
                    ];
                }
                $salesBySubline[$linea][$sublinea]['soles'] += $montoSoles;
                $salesBySubline[$linea][$sublinea]['dolares'] += $montoDolares;
                
                // Group Product
                $prodId = $item->producto_id;
                if ($producto && $prodId) {
                    if (!isset($salesByProduct[$prodId])) {
                        $salesByProduct[$prodId] = [
                            'producto_id' => $prodId,
                            'codigo' => $producto->codigo ?? 'N/A',
                            'nombre' => $producto->nombre ?? 'N/A',
                            'unidad_medida' => $producto->unidad_medida ?? 'Und',
                            'linea' => $linea,
                            'sublinea' => $sublinea,
                            'cantidad_vendida' => 0.0,
                            'soles' => 0.0,
                            'dolares' => 0.0
                        ];
                    }
                    
                    // Extract quantity
                    $campos = is_string($item->campos_json) ? json_decode($item->campos_json, true) : ($item->campos_json ?: []);
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
                    
                    $salesByProduct[$prodId]['cantidad_vendida'] += $cantidad;
                    $salesByProduct[$prodId]['soles'] += $montoSoles;
                    $salesByProduct[$prodId]['dolares'] += $montoDolares;
                }
            }

            $formattedSublines = [];
            foreach ($salesBySubline as $lineKey => $sublines) {
                $formattedSublines[$lineKey] = array_values($sublines);
            }

            $analiticaData = [
                'lineas' => array_values($salesByLine),
                'sublineas' => $formattedSublines,
                'productos' => array_values($salesByProduct)
            ];

            return view('dashboard.supervisor', compact(
                'startDate',
                'endDate',
                'preset',
                'rankingVendedoras',
                'totalVentasPeriodoSoles',
                'totalVentasPeriodoDolares',
                'totalCotizaciones',
                'convertidas',
                'eficienciaConversion',
                'pedidosPorProducir',
                'alertasRuptura',
                'vendedoras',
                'analiticaData'
            ));
        }

        // ROL VENDEDOR (Original)
        $estadosValidos = ['Aprobado', 'Despachado', 'Entregado'];
        $mesActual = Carbon::now();

        // Calcular ventas por moneda del mes actual respetando roles
        $pedidosMesQuery = Pedido::whereIn('estado', $estadosValidos)
            ->whereYear('fecha_pedido', $mesActual->year)
            ->whereMonth('fecha_pedido', $mesActual->month);

        if ($user->hasRole('Vendedor')) {
            $pedidosMesQuery->whereHas('cotizacion', function ($query) {
                $query->where('vendedor_id', auth()->id());
            });
        }

        $pedidosDelMes = $pedidosMesQuery->with('cotizacion')->get()->unique('cotizacion_id');

        $ventasSoles = $pedidosDelMes->sum(function ($pedido) {
            $c = $pedido->cotizacion;
            if ($c && $c->moneda === 'soles') {
                return $c->total ?? 0;
            }
            return 0;
        });

        $ventasDolares = $pedidosDelMes->sum(function ($pedido) {
            $c = $pedido->cotizacion;
            if ($c && $c->moneda === 'dolares') {
                return $c->total ?? 0;
            }
            return 0;
        });

        $data = [];
        $vendedorId = auth()->id();

        // Cantidad de Pedidos del Mes
        $data['cantidad_pedidos_mes'] = Pedido::whereHas('cotizacion', function ($query) use ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        })
            ->whereIn('estado', $estadosValidos)
            ->whereYear('fecha_pedido', $mesActual->year)
            ->whereMonth('fecha_pedido', $mesActual->month)
            ->count();
        
        // Últimos 5 Pedidos del Vendedor
        $data['ultimos_pedidos'] = Pedido::whereHas('cotizacion', function ($query) use ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        })
            ->with('cotizacion.cliente')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Top 5 Clientes VIP del Mes (por monto total en soles)
        $data['clientes_vip'] = Pedido::whereHas('cotizacion', function ($query) use ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        })
            ->whereIn('estado', $estadosValidos)
            ->whereYear('fecha_pedido', $mesActual->year)
            ->whereMonth('fecha_pedido', $mesActual->month)
            ->with('cotizacion.cliente')
            ->get()
            ->groupBy(function ($item) {
                return $item->cotizacion->cliente_id ?? 'N/A';
            })
            ->map(function ($group) {
                $first = $group->first();
                $montoTotal = $group->sum(function ($pedido) {
                    $c = $pedido->cotizacion;
                    if ($c && $c->moneda === 'dolares') {
                        return ($c->total ?? 0) * ($c->tipo_cambio ?? 1);
                    }
                    return $c->total ?? 0;
                });

                return (object)[
                    'cliente' => $first->cotizacion->cliente,
                    'total_pedidos' => $group->count(),
                    'total_monto' => $montoTotal
                ];
            })
            ->filter(fn($item) => $item->cliente !== null)
            ->sortByDesc('total_monto')
            ->take(5);
        
        // Productos más vendidos para este vendedor (volumen sumado de cantidades)
        $data['productos_top'] = DB::table('productos as p')
            ->join('cotizacion_items as ci', 'ci.producto_id', '=', 'p.id')
            ->join('cotizaciones as c', 'ci.cotizacion_id', '=', 'c.id')
            ->leftJoin('pedidos as pe', 'pe.cotizacion_id', '=', 'c.id')
            ->where('c.vendedor_id', $vendedorId)
            ->where('c.estado', '!=', 'Rechazada')
            ->select(
                'p.id',
                'p.nombre',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(ci.campos_json::jsonb->>'cantidad', '') AS NUMERIC), 
                    CAST(NULLIF(ci.campos_json::jsonb->>'total_millares', '') AS NUMERIC), 
                    CAST(NULLIF(ci.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(ci.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                )) as total_cotizado"),
                DB::raw("SUM(CASE WHEN pe.id IS NOT NULL AND pe.estado IN ('Aprobado', 'Despachado', 'Entregado') THEN 
                    COALESCE(
                        CAST(NULLIF(ci.campos_json::jsonb->>'cantidad', '') AS NUMERIC), 
                        CAST(NULLIF(ci.campos_json::jsonb->>'total_millares', '') AS NUMERIC), 
                        CAST(NULLIF(ci.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                        CAST(NULLIF(ci.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                        0
                    ) ELSE 0 END) as total_vendido")
            )
            ->groupBy('p.id', 'p.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get();
        
        $data['is_admin'] = false;

        return view('dashboard', compact('data', 'ventasSoles', 'ventasDolares'));
    }

    /**
     * AJAX endpoint to fetch the top 5 products sold by a specific saleswoman in a date range.
     */
    public function getVendedoraProductos(Request $request)
    {
        $vendedoraId = $request->input('vendedora_id');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        if (!$vendedoraId) {
            return response()->json(['error' => 'Vendedora ID es requerido'], 400);
        }

        $pedidosIds = Pedido::where('user_id', $vendedoraId)
            ->whereIn('estado', ['Aprobado', 'Despachado', 'Entregado'])
            ->whereBetween('fecha_pedido', [$startDate, $endDate])
            ->pluck('id');

        $items = \App\Models\PedidoItem::whereIn('pedido_id', $pedidosIds)
            ->with(['producto', 'pedido.cotizacion'])
            ->get();

        $productosTop = $items->groupBy('producto_id')->map(function ($group) {
            $firstItem = $group->first();
            $productoName = $firstItem->producto->nombre ?? 'N/A';
            $productoCode = $firstItem->producto->codigo ?? 'N/A';
            $unidadMedida = $firstItem->producto->unidad_medida ?? 'Und';

            $totalCantidad = 0.0;
            $montoSoles = 0.0;
            $montoDolares = 0.0;

            foreach ($group as $item) {
                $campos = json_decode($item->campos_json, true) ?: [];
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
                $totalCantidad += $cantidad;

                $pedido = $item->pedido;
                if ($pedido) {
                    $moneda = $pedido->cotizacion ? $pedido->cotizacion->moneda : ($pedido->cantidades_json['moneda'] ?? 'soles');
                    $precioFila = (float)$item->precio_total;

                    if ($moneda === 'dolares') {
                        $montoDolares += $precioFila;
                    } else {
                        $montoSoles += $precioFila;
                    }
                }
            }

            return [
                'producto_id' => $firstItem->producto_id,
                'codigo' => $productoCode,
                'nombre' => $productoName,
                'unidad_medida' => $unidadMedida,
                'cantidad_vendida' => round($totalCantidad, 3),
                'monto_soles' => round($montoSoles, 2),
                'monto_dolares' => round($montoDolares, 2),
            ];
        })->sortByDesc('cantidad_vendida')->take(5)->values()->all();

        return response()->json($productosTop);
    }
}