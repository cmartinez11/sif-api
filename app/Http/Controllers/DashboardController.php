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
    public function index()
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

            return view('dashboard.logistica', compact(
                'despachosHoy', 'pendientesPicking', 'backordersEspera', 'entregadosSemana', 'agendaDespachos'
            ));
        }

        $data = [];

        if ($user->hasAnyRole(['Supervisor', 'Administrador'])) {
            // Panel Admin: Métricas generales
            $estadosValidos = ['Aprobado', 'Despachado', 'Entregado'];
            $data['total_pedidos'] = Pedido::whereIn('estado', $estadosValidos)->count();
            $data['total_cotizaciones'] = Cotizacion::count();
            
            // Vendedor con más pedidos (Solo Ventas Reales)
            $topVendedor = Pedido::whereIn('pedidos.estado', $estadosValidos)
                ->join('cotizaciones', 'pedidos.cotizacion_id', '=', 'cotizaciones.id')
                ->selectRaw('cotizaciones.vendedor_id, count(pedidos.id) as total')
                ->groupBy('cotizaciones.vendedor_id')
                ->orderBy('total', 'desc')
                ->first();
            
            if ($topVendedor) {
                $vendedor = User::find($topVendedor->vendedor_id);
                $data['top_vendedor'] = $vendedor->name ?? 'N/A';
                $data['top_vendedor_pedidos'] = $topVendedor->total;
            }

            // Producto Más Vendido (Solo Ventas Reales)
            $topProduct = CotizacionItem::join('cotizaciones', 'cotizacion_items.cotizacion_id', '=', 'cotizaciones.id')
                ->join('pedidos', 'pedidos.cotizacion_id', '=', 'cotizaciones.id')
                ->whereIn('pedidos.estado', $estadosValidos)
                ->selectRaw('cotizacion_items.producto_id, count(cotizacion_items.producto_id) as freq')
                ->groupBy('cotizacion_items.producto_id')
                ->orderBy('freq', 'desc')
                ->first();
            
            if ($topProduct) {
                $data['top_producto'] = $topProduct->producto->nombre ?? 'N/A';
            }
            
            $data['is_admin'] = true;
        } else {
            // Panel Vendedor: Datos personalizados y seguros (Basado en PEDIDOS)
            $vendedorId = auth()->id();
            
            $estadosValidos = ['Aprobado', 'Despachado', 'Entregado'];
            $mesActual = Carbon::now();
            $data['ventas_mes'] = Pedido::whereHas('cotizacion', function ($query) use ($vendedorId) {
                $query->where('vendedor_id', $vendedorId);
            })
                ->whereIn('estado', $estadosValidos)
                ->whereYear('fecha_pedido', $mesActual->year)
                ->whereMonth('fecha_pedido', $mesActual->month)
                ->with('cotizacion')
                ->get()
                ->unique('cotizacion_id')
                ->sum(function ($pedido) {
                    $c = $pedido->cotizacion;
                    if ($c && $c->moneda === 'dolares') {
                        return ($c->total ?? 0) * ($c->tipo_cambio ?? 1);
                    }
                    return $c->total ?? 0;
                });
            
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
        }

        return view('dashboard', compact('data'));
    }
}