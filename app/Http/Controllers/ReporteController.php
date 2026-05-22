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

        // Cruzar productos con las ventas sumadas del día
        $productosReporte = DB::table('productos')
            ->leftJoinSub($ventasHoySub, 'ventas', function ($join) {
                $join->on('productos.id', '=', 'ventas.producto_id');
            })
            ->select(
                'productos.codigo',
                'productos.nombre',
                'productos.linea',
                'productos.unidad_medida_logistica',
                'productos.stock',
                'productos.deuda_arrastrada',
                DB::raw('COALESCE(ventas.total_vendido, 0.000) as vendido_hoy')
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

        $productosReporte = DB::table('productos')
            ->leftJoinSub($ventasHoySub, 'ventas', function ($join) {
                $join->on('productos.id', '=', 'ventas.producto_id');
            })
            ->select(
                'productos.codigo',
                'productos.nombre',
                'productos.linea',
                'productos.unidad_medida_logistica',
                'productos.stock',
                'productos.deuda_arrastrada',
                DB::raw('COALESCE(ventas.total_vendido, 0.000) as vendido_hoy')
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
}
