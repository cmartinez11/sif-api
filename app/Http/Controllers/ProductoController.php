<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function __construct()
    {
        // Esta línea protege el sistema: Solo Admin y Supervisor manejan escritura.
        $this->middleware('role:Administrador|Supervisor')->except(['index','show', 'monitoreoStock']);
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

        // Evitar el error 500 de Postgres permitiendo un valor inicial decimal limpio
        $validated['stock'] = 0.000; 

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
        $rules = [
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'nullable|string|max:255',
            'precio_base' => 'required|numeric|min:0',
            'linea' => 'required|string|max:255',
            'sublinea' => 'nullable|string|max:255',
            'estado' => 'required|boolean',
            'peso' => 'nullable|numeric|min:0',
            'unidad_medida_logistica' => 'nullable|string|max:255',
        ];

        $isAuthorized = auth()->user() && auth()->user()->hasAnyRole(['Supervisor', 'Administrador']);

        if ($isAuthorized) {
            $rules['stock'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $oldStock = (float)$producto->stock;
        $newStock = $isAuthorized && $request->has('stock')
            ? ($request->stock !== null && $request->stock !== '' ? (float)$request->stock : 0.0)
            : $oldStock;

        if ($isAuthorized && $request->has('stock')) {
            $validated['stock'] = $newStock;
        }

        $producto->update($validated);

        if ($isAuthorized && $oldStock !== $newStock) {
            $ipAddress = null;
            if (!app()->runningInConsole()) {
                try {
                    $ipAddress = request() ? request()->ip() : null;
                } catch (\Exception $e) {
                    $ipAddress = null;
                }
            }

            $userName = auth()->user() ? auth()->user()->name : 'Supervisor';
            \App\Models\Log::create([
                'user_id' => auth()->id(),
                'accion' => 'MODIFICAR',
                'modulo' => 'Productos',
                'registro_id' => $producto->id,
                'descripcion' => "El usuario {$userName} modificó el stock del producto {$producto->nombre} (de {$oldStock} a {$newStock}).",
                'historial_json' => [
                    'stock' => [
                        'antes' => $oldStock,
                        'despues' => $newStock,
                    ]
                ],
                'ip_address' => $ipAddress,
            ]);
        }

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }

    public function cargarStockDiario(Request $request)
    {
        $request->validate([
            'archivo_stock' => 'required|file',
        ]);

        $file = $request->file('archivo_stock');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xls', 'csv', 'txt'])) {
            return redirect()->back()->withErrors(['archivo_stock' => 'El archivo debe tener extensión .xls, .csv o .txt.']);
        }

        $path = $file->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return redirect()->back()->with('error', 'No se pudo leer el archivo.');
        }

        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($lines as $index => $linea) {
                if ($index === 0) {
                    continue; // Saltar cabecera
                }

                $parts = explode("\t", $linea);

                $codigo = isset($parts[0]) ? trim($parts[0]) : '';
                $stockActualRaw = isset($parts[1]) ? trim($parts[1]) : '';

                if ($codigo === '' && $stockActualRaw === '') {
                    continue;
                }

                if ($codigo === '') {
                    throw new \Exception("Fila " . ($index + 1) . ": El código del producto está vacío.");
                }

                if ($stockActualRaw === '') {
                    throw new \Exception("Fila " . ($index + 1) . ": Falta el valor del stock para el producto '$codigo'.");
                }

                // Limpieza preventiva: Reemplazar comas por puntos si el Excel viene con formato regional
                $stockActualRaw = str_replace(',', '.', $stockActualRaw);

                if (!is_numeric($stockActualRaw)) {
                    throw new \Exception("Fila " . ($index + 1) . ": El valor del stock actual '{$stockActualRaw}' para el producto '{$codigo}' no es un número válido.");
                }

                // El casteo a float ahora mantendrá los 3 decimales exactos en la base de datos modificada
                $stockActual = (float)$stockActualRaw;

                // Buscar el producto en la BD para ver su estado actual de stock y amortización
                $producto = DB::table('productos')
                    ->where('codigo', $codigo)
                    ->select('id', 'stock', 'deuda_arrastrada', 'ultimo_stock_cargado_at')
                    ->first();

                if ($producto) {
                    $hoy = date('Y-m-d');
                    $deuda = (float)($producto->deuda_arrastrada ?? 0.000);
                    $ultimoCargadoAt = $producto->ultimo_stock_cargado_at;

                    // Si la última carga no fue hoy, evaluamos la deuda arrastrada desde el stock actual
                    if (empty($ultimoCargadoAt) || $ultimoCargadoAt !== $hoy) {
                        $stockAnterior = (float)($producto->stock ?? 0.000);
                        if ($stockAnterior < 0.0) {
                            $deuda = $stockAnterior; // Guardamos el stock negativo como deuda
                        } else {
                            $deuda = 0.000;
                        }
                    }

                    // Calculamos el nuevo stock neto: nueva carga (subido) + deuda arrastrada (negativa)
                    $nuevoStock = (float)round($stockActual + $deuda, 3);

                    $updated = DB::table('productos')
                        ->where('id', $producto->id)
                        ->update([
                            'stock' => $nuevoStock,
                            'deuda_arrastrada' => $deuda,
                            'ultimo_stock_cargado_at' => $hoy,
                            'updated_at' => now(),
                        ]);

                    if ($updated) {
                        $updatedCount++;
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', "Stock actualizado exitosamente para {$updatedCount} productos.");
    }

    public function descargarPlantillaStock()
    {
        $headers = [
            "Content-type"        => "text/tab-separated-values",
            "Content-Disposition" => "attachment; filename=plantilla_stock_diario.xls",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        $columns = ['CDG_PROD', 'STK_ACT'];
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fwrite($file, implode("\t", $columns) . "\n");
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtener stock actual y ventas del día agrupadas por vendedora para un producto específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function monitoreoStock($id)
    {
        $producto = DB::table('productos')->select('codigo', 'nombre', 'stock')->where('id', $id)->first();
        if (!$producto) {
            return response()->json(['error' => 'Producto no encontrado.'], 404);
        }

        $stock = number_format((float) $producto->stock, 3, '.', '');

        // Obtener ventas del día de hoy agrupadas por vendedora y pedido
        $ventasHoy = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('users', 'pedidos.user_id', '=', 'users.id')
            ->where('pedido_items.producto_id', $id)
            ->where('pedidos.fecha_pedido', date('Y-m-d'))
            ->whereNotIn('pedidos.estado', ['Anulado', 'Cancelado por el cliente', 'Rechazado'])
            ->select(
                'users.name as vendedora',
                'pedidos.numero as numero_pedido',
                DB::raw("SUM(COALESCE(
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_kilos', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'total_millares', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'fardo', '') AS NUMERIC),
                    CAST(NULLIF(pedido_items.campos_json::jsonb->>'cantidad_fardos', '') AS NUMERIC),
                    0
                )) as cantidad_vendida")
            )
            ->groupBy('users.name', 'pedidos.numero')
            ->orderBy('pedidos.numero')
            ->get();

        $ventasAgrupadas = $ventasHoy->map(function ($venta) {
            return [
                'vendedora' => $venta->vendedora,
                'pedido' => $venta->numero_pedido,
                'cantidad' => number_format((float) $venta->cantidad_vendida, 3, '.', '')
            ];
        });

        return response()->json([
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre,
            'stock' => $stock,
            'ventas_hoy' => $ventasAgrupadas
        ]);
    }
}