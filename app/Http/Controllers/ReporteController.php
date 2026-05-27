<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * ReporteController constructor.
     * Restringe el acceso únicamente a usuarios autenticados con los roles de Administrador o Supervisor.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:Administrador|Supervisor|Logistico']);
    }

    /**
     * Procesa la auditoría del día utilizando Query Builder de Laravel.
     * Cruza la tabla 'productos' con 'pedido_items' y 'pedidos' para obtener:
     * Código, Nombre del producto, Línea, Unidad de Medida Logística,
     * sumatoria de la cantidad vendida hoy (excluyendo 'Rechazado' y 'Anulado'),
     * y el stock remanente actual.
     *
     * @return \Illuminate\View\View
     */
    public function reporteCierreDiario()
    {
        $hoy = date('Y-m-d');

        // Subconsulta para agrupar y sumar la cantidad vendida HOY por producto
        $ventasHoySub = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.fecha_pedido', $hoy)
            ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
            ->select(
                'pedido_items.producto_id',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                )) as total_vendido")
            )
            ->groupBy('pedido_items.producto_id');

        // 1. CALCULAR EL STOCK COMPROMETIDO FUTURO (FechaDespacho >= Hoy + 2 días, Aprobado)
        $fechaLimite = now()->addDays(1)->toDateString();
        $stockComprometidoSub = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->whereIn('pedidos.estado', ['Aprobado', 'Pendiente'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->select(
                'pedido_items.producto_id',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                )) as total_comprometido")
            )
            ->groupBy('pedido_items.producto_id');

        // 2. CALCULAR LAS VENTAS DE HOY QUE SE DESPACHAN A FUTURO (FechaDespacho >= Hoy + 2 días)
        // para compensar el doble descuento en la fórmula final
        $ventasHoyFuturoSub = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.fecha_pedido', $hoy)
            ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->select(
                'pedido_items.producto_id',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                )) as total_vendido_futuro")
            )
            ->groupBy('pedido_items.producto_id');

        // Cruzar productos con las ventas sumadas del día, el stock comprometido y las ventas futuras de hoy
        $productosReporte = DB::table('productos')
            ->leftJoinSub($ventasHoySub, 'ventas', function ($join) {
                $join->on('productos.id', '=', 'ventas.producto_id');
            })
            ->leftJoinSub($stockComprometidoSub, 'comprometido', function ($join) {
                $join->on('productos.id', '=', 'comprometido.producto_id');
            })
            ->leftJoinSub($ventasHoyFuturoSub, 'ventas_futuras', function ($join) {
                $join->on('productos.id', '=', 'ventas_futuras.producto_id');
            })
            ->select(
                'productos.codigo',
                'productos.nombre',
                'productos.linea',
                'productos.unidad_medida_logistica',
                'productos.stock',
                'productos.deuda_arrastrada',
                DB::raw('COALESCE(ventas.total_vendido, 0.000) as vendido_hoy'),
                DB::raw('COALESCE(comprometido.total_comprometido, 0.000) as stock_comprometido'),
                DB::raw('COALESCE(ventas_futuras.total_vendido_futuro, 0.000) as vendido_hoy_futuro')
            )
            ->orderBy('productos.codigo')
            ->get();

        return view('reportes.cierre_diario', compact('productosReporte'));
    }

    /**
     * Genera y descarga un archivo PDF con la auditoría de stock diario.
     *
     * @return \Illuminate\Http\Response
     */
    public function descargarCierreDiario()
    {
        $hoy = date('Y-m-d');

        // Consulta idéntica para extraer stock y volumen vendido hoy
        $ventasHoySub = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.fecha_pedido', $hoy)
            ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
            ->select(
                'pedido_items.producto_id',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                )) as total_vendido")
            )
            ->groupBy('pedido_items.producto_id');

        // Calcular el stock comprometido futuro
        $fechaLimite = now()->addDays(1)->toDateString();
        $stockComprometidoSub = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->whereIn('pedidos.estado', ['Aprobado', 'Pendiente'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->select(
                'pedido_items.producto_id',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                )) as total_comprometido")
            )
            ->groupBy('pedido_items.producto_id');

        // Calcular las ventas de hoy que se despachan a futuro
        $ventasHoyFuturoSub = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedidos.fecha_pedido', $hoy)
            ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->select(
                'pedido_items.producto_id',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                )) as total_vendido_futuro")
            )
            ->groupBy('pedido_items.producto_id');

        $productosReporte = DB::table('productos')
            ->leftJoinSub($ventasHoySub, 'ventas', function ($join) {
                $join->on('productos.id', '=', 'ventas.producto_id');
            })
            ->leftJoinSub($stockComprometidoSub, 'comprometido', function ($join) {
                $join->on('productos.id', '=', 'comprometido.producto_id');
            })
            ->leftJoinSub($ventasHoyFuturoSub, 'ventas_futuras', function ($join) {
                $join->on('productos.id', '=', 'ventas_futuras.producto_id');
            })
            ->select(
                'productos.codigo',
                'productos.nombre',
                'productos.linea',
                'productos.unidad_medida_logistica',
                'productos.stock',
                'productos.deuda_arrastrada',
                DB::raw('COALESCE(ventas.total_vendido, 0.000) as vendido_hoy'),
                DB::raw('COALESCE(comprometido.total_comprometido, 0.000) as stock_comprometido'),
                DB::raw('COALESCE(ventas_futuras.total_vendido_futuro, 0.000) as vendido_hoy_futuro')
            )
            ->orderBy('productos.codigo')
            ->get();

        // Convertir logo corporativo a Base64 si existe en public/logo2.png
        $logoBase64 = null;
        $path = public_path('logo2.png');
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $pdf = Pdf::loadView('reportes.cierre_diario_pdf', [
            'productosReporte' => $productosReporte,
            'logoBase64' => $logoBase64
        ]);

        return $pdf->download("cierre-diario-{$hoy}.pdf");
    }

    /**
     * Devuelve el desglose detallado de los movimientos que afectan el stock de un producto el día de hoy.
     *
     * @param string $codigo
     * @return \Illuminate\Http\JsonResponse
     */
    public function diagnosticoStock($codigo)
    {
        $producto = DB::table('productos')
            ->where('codigo', $codigo)
            ->first();

        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado.'], 404);
        }

        $hoy = date('Y-m-d');

        // Las filas de 'pedido_items' donde aparezca ese producto hoy
        $ventasHoy = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('users', 'pedidos.user_id', '=', 'users.id')
            ->where('pedido_items.producto_id', $producto->id)
            ->where('pedidos.fecha_pedido', $hoy)
            ->select(
                'pedidos.numero as numero_pedido',
                'users.name as vendedora',
                'pedidos.estado as estado_pedido',
                DB::raw("COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                ) as cantidad_vendida")
            )
            ->orderBy('pedidos.numero')
            ->get();

        // Suma de ventas hoy (excluyendo 'Rechazado' y 'Anulado') para calcular stock de la mañana
        $ventaHoySum = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedido_items.producto_id', $producto->id)
            ->where('pedidos.fecha_pedido', $hoy)
            ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
            ->select(DB::raw("SUM(COALESCE(
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                0
            )) as total_vendido"))
            ->first();

        $vendidoHoy = (float)($ventaHoySum->total_vendido ?? 0);

        // Stock que vino en la plantilla de 'Sincronizar Stock' de la mañana
        // Formula: (productos.stock - productos.deuda_arrastrada) + ventas_hoy
        $stockPlanilla = (float)$producto->stock - (float)$producto->deuda_arrastrada + $vendidoHoy;

        // Comprometido futuro: Pedidos aprobados a 2 días a más
        $fechaLimite = now()->addDays(1)->toDateString();
        $comprometidos = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('users', 'pedidos.user_id', '=', 'users.id')
            ->where('pedido_items.producto_id', $producto->id)
            ->whereIn('pedidos.estado', ['Aprobado', 'Pendiente'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->select(
                'pedidos.numero as numero_pedido',
                'users.name as vendedora',
                'pedidos.fecha_entrega_confirmada',
                'pedidos.estado as estado_pedido',
                DB::raw("COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                    0
                ) as cantidad_comprometida")
            )
            ->orderBy('pedidos.fecha_entrega_confirmada')
            ->get();

        $totalComprometido = (float)$comprometidos->sum('cantidad_comprometida');

        // Ventas de hoy que se despachan a futuro para compensar la doble carga
        $ventaHoyFuturoSumObj = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->where('pedido_items.producto_id', $producto->id)
            ->where('pedidos.fecha_pedido', $hoy)
            ->whereNotIn('pedidos.estado', ['Rechazado', 'Anulado'])
            ->whereDate('pedidos.fecha_entrega_confirmada', '>=', $fechaLimite)
            ->select(DB::raw("SUM(COALESCE(
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_millar', '') AS NUMERIC),
                0
            )) as total_vendido_futuro"))
            ->first();
        $vendidoHoyFuturo = (float)($ventaHoyFuturoSumObj->total_vendido_futuro ?? 0);

        // Saldo SIF (Disponible) = stock - total_comprometido + vendidoHoyFuturo
        $saldoSif = (float)$producto->stock - $totalComprometido + $vendidoHoyFuturo;

        return response()->json([
            'producto' => [
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'stock_actual' => (float)$producto->stock,
                'deuda_arrastrada' => (float)$producto->deuda_arrastrada,
                'stock_planilla' => $stockPlanilla,
                'comprometido_futuro' => $totalComprometido,
                'vendido_hoy_futuro' => $vendidoHoyFuturo,
                'saldo_sif' => $saldoSif,
                'unidad' => $producto->unidad_medida_logistica ?? 'N/A',
            ],
            'ventas_hoy' => $ventasHoy->map(function ($item) {
                return [
                    'pedido' => $item->numero_pedido,
                    'vendedora' => $item->vendedora,
                    'cantidad' => (float)$item->cantidad_vendida,
                    'estado' => $item->estado_pedido,
                ];
            }),
            'comprometidos_futuros' => $comprometidos->map(function ($item) {
                return [
                    'pedido' => $item->numero_pedido,
                    'vendedora' => $item->vendedora,
                    'fecha_entrega' => $item->fecha_entrega_confirmada,
                    'cantidad' => (float)$item->cantidad_comprometida,
                    'estado' => $item->estado_pedido,
                ];
            })
        ]);
    }
}
