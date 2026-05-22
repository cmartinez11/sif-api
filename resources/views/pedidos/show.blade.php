<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Detalle de Pedido') }}: {{ $pedido->numero }}
            </h2>
            <div class="flex items-center space-x-3">
                @unlessrole('Logistico')
                    <a href="{{ route('pedidos.pdf', $pedido) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descargar PDF
                    </a>
                @endunlessrole
                <a href="{{ route('pedidos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150">
                    Volver al Listado
                </a>
            </div>
        </div>
        <style> [x-cloak] { display: none !important; } </style>
    </x-slot>

    <div x-data="pedidoDetalle()" class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 items-start">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Información General</h3>
                            <p class="text-lg font-bold text-gray-900">Pedido #{{ $pedido->numero }}</p>
                            <p class="text-sm text-gray-600">Fecha Pedido: {{ \Carbon\Carbon::parse($pedido->fecha_pedido)->format('d/m/Y') }}</p>
                            <div class="mt-3">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    @if($pedido->estado == 'Pendiente') bg-yellow-100 text-yellow-800 
                                    @elseif($pedido->estado == 'Ajustado por Logística') bg-orange-100 text-orange-800
                                    @elseif($pedido->estado == 'Aprobado') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ $pedido->estado }}
                                </span>
                            </div>
                            @hasanyrole('Vendedor|Administrador|Supervisor')
                                @if(str_contains($pedido->numero, '-') && $pedido->estado === 'Pendiente')
                                    <form id="form-cancelar-saldo" action="{{ route('pedidos.cancelar_backorder', $pedido->numero ?? $pedido->id) }}" method="POST" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="proveedor_nombre" :value="perdidaData.proveedor_nombre">
                                        <input type="hidden" name="motivo_perdida" :value="perdidaData.motivo_perdida">
                                        <input type="hidden" name="precio_ofrecido" :value="perdidaData.precio_ofrecido">
                                        <input type="hidden" name="entrega_proveedor" :value="perdidaData.entrega_proveedor">
                                        <input type="hidden" name="entrega_nuestra" :value="perdidaData.entrega_nuestra">
                                        <input type="hidden" name="detalle_perdida" :value="perdidaData.detalle_perdida">

                                        <button type="button" @click.prevent="abrirModalPerdida()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow-md text-[10px] transition duration-150 uppercase tracking-widest">
                                            Cancelar Saldo Pendiente
                                        </button>
                                    </form>
                                @endif
                            @endhasanyrole
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Cliente</h3>
                            <p class="text-lg font-bold text-gray-900">{{ $pedido->cotizacion->cliente->nombre }}</p>
                            <p class="text-sm text-gray-600">RUC: {{ $pedido->cotizacion->cliente->ruc }}</p>
                            <p class="text-sm text-gray-600">Condición: {{ $pedido->cotizacion->condicion_pago }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Origen (Vendedor)</h3>
                            <p class="text-lg font-bold text-gray-900">{{ $pedido->vendedor->name ?? ($pedido->cotizacion->vendedor->name ?? 'N/A') }}</p>
                            <p class="text-sm text-gray-600">Cotización: #{{ $pedido->cotizacion->numero }}</p>
                            <p class="text-sm text-gray-600">Plantilla: {{ $pedido->cotizacion->plantilla->nombre }}</p>
                        </div>
                        <div class="p-4 bg-red-50 rounded-lg border border-red-100 shadow-sm">
                            <h3 class="text-xs font-bold {{ $pedido->fecha_entrega_confirmada ? 'text-green-700' : 'text-red-600' }} uppercase tracking-wider mb-2">
                                {{ $pedido->fecha_entrega_confirmada ? 'FECHA CONFIRMADA' : 'FECHA POR CONFIRMAR' }}
                            </h3>
                            <p class="text-[10px] text-gray-500 mb-2">
                                Estimada por ventas: <span class="font-bold">{{ $pedido->cotizacion->fecha_entrega_estimada ? \Carbon\Carbon::parse($pedido->cotizacion->fecha_entrega_estimada)->format('d/m/Y') : 'No asignada' }}</span>
                            </p>

                            {{--
                                El formulario de "CONFIRMAR FECHA" de esta cabecera se oculta para el
                                rol Logístico. Ellos confirman la fecha directamente en el footer del
                                form de ajuste de cantidades (junto al select Estado de Producción),
                                evitando así el campo duplicado en su interfaz.
                            --}}
                            @hasanyrole('Vendedor|Administrador|Supervisor')
                                @if(!in_array($pedido->estado, ['Aprobado', 'Despachado', 'Entregado', 'Cancelado por el cliente', 'Cancelado']))
                                    <form action="{{ route('pedidos.confirmar_fecha', $pedido->numero ?? $pedido->id) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <input type="date" name="fecha_entrega_confirmada"
                                            value="{{ old('fecha_entrega_confirmada', $pedido->fecha_entrega_confirmada ? $pedido->fecha_entrega_confirmada->format('Y-m-d') : ($pedido->cotizacion->fecha_entrega_estimada ? \Carbon\Carbon::parse($pedido->cotizacion->fecha_entrega_estimada)->format('Y-m-d') : '')) }}"
                                            class="w-full text-sm border-red-300 focus:ring-red-500 focus:border-red-500 rounded-md shadow-sm font-bold text-red-700">

                                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-xs transition duration-150 uppercase tracking-widest shadow-md">
                                            CONFIRMAR FECHA
                                        </button>
                                    </form>
                                @else
                                    <div class="bg-white/50 border border-red-200 rounded-lg p-3 text-center">
                                        <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Fecha de Despacho Confirmada</p>
                                        <p class="text-lg font-black text-red-700">
                                            @if(in_array($pedido->estado, ['Cancelado por el cliente', 'Cancelado']))
                                                <span class="text-red-500">PEDIDO CANCELADO</span>
                                            @else
                                                {{ $pedido->fecha_entrega_confirmada ? $pedido->fecha_entrega_confirmada->format('d/m/Y') : 'Por definir' }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            @else
                                {{-- Para Logístico: muestra solo la fecha actual (solo lectura) --}}
                                <div class="bg-white/50 border border-red-200 rounded-lg p-3 text-center">
                                    <p class="text-[10px] text-gray-500 uppercase font-bold mb-1">Fecha de Despacho</p>
                                    <p class="text-lg font-black text-red-700">
                                        {{ $pedido->fecha_entrega_confirmada ? $pedido->fecha_entrega_confirmada->format('d/m/Y') : 'Por confirmar' }}
                                    </p>
                                    @if($pedido->estado_produccion)
                                        <span class="mt-2 inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                            {{ $pedido->estado_produccion === 'PRODUCIDO' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $pedido->estado_produccion }}
                                        </span>
                                    @endif
                                </div>
                            @endhasanyrole
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Ítems del Pedido</h3>
                    </div>

                    <form action="{{ route('pedidos.ajustar_cantidades', $pedido->numero ?? $pedido->id) }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                        
                                        @if($pedido->cotizacion->plantilla->nombre == 'Tratadas' || $pedido->cotizacion->plantilla->nombre == 'Bolsas de Polipropileno')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fardo</th>
                                            @unlessrole('Logistico')
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ $pedido->cotizacion->plantilla->nombre == 'Tratadas' ? 'Cant. x Millar' : 'Unidades x Fardo' }}
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ $pedido->cotizacion->plantilla->nombre == 'Tratadas' ? 'Total Millares' : 'Total Kilos' }}
                                                </th>
                                            @endunlessrole
                                        @elseif($pedido->cotizacion->plantilla->nombre == 'Bolsas de Polipropileno por kilos')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cant. de Fardos</th>
                                            @unlessrole('Logistico')
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">U/M</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Kilos</th>
                                            @endunlessrole
                                        @elseif($pedido->cotizacion->plantilla->nombre == 'Pets')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CANT. SACO/CAJAS/ BOLSAS/ JUMBO</th>
                                            @unlessrole('Logistico')
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cant. x Millar</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Millares</th>
                                            @endunlessrole
                                        @else
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cant. Original / U.M</th>
                                        @endif

                                        @if(auth()->user()->hasAnyRole(['Logistico', 'Supervisor', 'Administrador']) && in_array($pedido->estado, ['Pendiente', 'En Revisión']))
                                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-600 uppercase tracking-wider">Ajuste Logística</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                                        @endif
                                        
                                        @unlessrole('Logistico')
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Item</th>
                                        @endunlessrole
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php 
                                        $sumatoriaSubtotal = 0; 
                                        $moneda = strtolower($pedido->moneda ?? $pedido->cotizacion->moneda ?? 'soles');
                                        $simboloMoneda = in_array($moneda, ['dolares', 'usd']) ? 'US$ ' : 'S/. ';
                                    @endphp
                                    @foreach($pedido->items as $item)
                                        @php
                                            $campos = json_decode($item->campos_json, true);
                                            $nombrePlantilla = $pedido->cotizacion->plantilla->nombre;
                                                                                    
                                            // 1. LECTURA DE AJUSTES
                                            $despachos = is_string($pedido->cantidades_despachadas) ? json_decode($pedido->cantidades_despachadas, true) : ($pedido->cantidades_despachadas ?? []);
                                            $isBackorder = str_contains($pedido->numero, '-');
                                            $tieneAjuste = false;
                                            $ajusteQty = null;

                                            if (is_array($despachos) && count($despachos) > 0) {
                                                if (array_key_exists($item->id, $despachos)) {
                                                    $tieneAjuste = true;
                                                    $ajusteQty = (float) $despachos[$item->id];
                                                } else {
                                                    // Mapeo por producto para backorders antiguos migrados o desajustes de ID
                                                    static $prodMap = null;
                                                    if ($prodMap === null) {
                                                        $prodMap = [];
                                                        if ($pedido->cotizacion && $pedido->cotizacion->items) {
                                                            foreach ($pedido->cotizacion->items as $cotItem) {
                                                                if (array_key_exists($cotItem->id, $despachos)) {
                                                                    $prodMap[$cotItem->producto_id] = $despachos[$cotItem->id];
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if (array_key_exists($item->producto_id, $prodMap)) {
                                                        $tieneAjuste = true;
                                                        $ajusteQty = (float) $prodMap[$item->producto_id];
                                                    }
                                                }
                                            } else {
                                                if ($isBackorder) {
                                                    $tieneAjuste = true;
                                                }
                                            }

                                            // 2. FILTRO BACKORDER
                                            if ($isBackorder && !$tieneAjuste) {
                                                continue; 
                                            }

                                            // 3. EXTRACCIÓN DE CANTIDADES
                                            $cantidadOriginal = 0;
                                            if (in_array($nombrePlantilla, ['Tratadas', 'Bolsas de Polipropileno', 'Pets'])) {
                                                $cantidadOriginal = (float) ($campos['fardo'] ?? 0);
                                            } elseif ($nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                                                $cantidadOriginal = (float) ($campos['cantidad_fardos'] ?? 0);
                                            } else {
                                                $cantidadOriginal = (float) ($campos['cantidad'] ?? 0);
                                            }

                                            $cantidadFinal = ($ajusteQty !== null) ? $ajusteQty : $cantidadOriginal;
                                            $huboCambio = $tieneAjuste && ($ajusteQty !== null) && ($cantidadFinal != $cantidadOriginal);
                                            
                                            $unidadVisual = $item->unidad_medida ?? ($item->producto->unidad_medida ?? '');

                                            // 4. RECÁLCULO MATEMÁTICO
                                            $precioTotalFila = $item->precio_total; 
                                            if (in_array($nombrePlantilla, ['Tratadas', 'Pets'])) {
                                                $cantidadPorMillar = (float) ($campos['cantidad_millar'] ?? 0);
                                                $totalDerivado = $cantidadFinal * $cantidadPorMillar; 
                                                $precioTotalFila = $totalDerivado * (float)$item->precio_unitario;
                                            } elseif ($nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                                                $fardosOriginalesCalc = $cantidadOriginal > 0 ? $cantidadOriginal : 1;
                                                $kilosOriginales = (float)($campos['total_kilos'] ?? 0);
                                                $pesoPromedio = $kilosOriginales / $fardosOriginalesCalc;
                                                $totalDerivado = $cantidadFinal * $pesoPromedio;
                                                $precioTotalFila = $totalDerivado * (float)$item->precio_unitario;
                                            } elseif ($nombrePlantilla === 'Universal') {
                                                $precioTotalFila = $cantidadFinal * (float)$item->precio_unitario;
                                            }

                                            $sumatoriaSubtotal += $precioTotalFila;
                                            
                                            $showAjuste = auth()->user()->hasAnyRole(['Logistico', 'Supervisor', 'Administrador']) && in_array($pedido->estado, ['Pendiente', 'En Revisión']);
                                        @endphp

                                        <tr @if($showAjuste) x-data="{ cantOriginal: {{ $cantidadFinal }}, ajuste: {{ $cantidadFinal }} }" @endif>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->producto->nombre }}</div>
                                                <div class="text-xs text-gray-500">Cód: {{ $item->producto->codigo }}</div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                @if($huboCambio)
                                                    <div class="flex flex-col">
                                                        <span class="text-gray-400 line-through text-xs" title="Cantidad Original">
                                                            {{ number_format($cantidadOriginal, 2) }} {{ $unidadVisual }}
                                                        </span>
                                                        <span class="text-red-600 font-bold" title="Cantidad Ajustada">
                                                            {{ number_format($cantidadFinal, 2) }} {{ $unidadVisual }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-800 font-bold text-red-600">
                                                        {{ number_format($cantidadFinal, 2) }} {{ $unidadVisual }}
                                                    </span>
                                                @endif
                                            </td>

                                            @if($nombrePlantilla == 'Tratadas' || $nombrePlantilla == 'Bolsas de Polipropileno')
                                                @hasanyrole('Vendedor|Administrador|Supervisor')
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                                        {{ number_format((float)($nombrePlantilla == 'Tratadas' ? ($campos['cantidad_millar'] ?? 0) : ($campos['cantidad'] ?? 0)), 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                                        {{ number_format((float)($nombrePlantilla == 'Tratadas' ? ($cantidadFinal * (float)($campos['cantidad_millar'] ?? 0)) : ($campos['total_kilos'] ?? 0)), 2) }}
                                                    </td>
                                                @else
                                                    <td colspan="2" class="px-6 py-4 text-center text-gray-400 bg-gray-50 text-xs italic">Oculto</td>
                                                @endhasanyrole
                                            @elseif($nombrePlantilla == 'Bolsas de Polipropileno por kilos')
                                                @hasanyrole('Vendedor|Administrador|Supervisor')
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                                        {{ $campos['unidad_medida'] ?? 'Kilos' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                                        {{ number_format((float)($cantidadFinal * ($cantidadOriginal > 0 ? ((float)($campos['total_kilos'] ?? 0) / $cantidadOriginal) : 0)), 2) }}
                                                    </td>
                                                @else
                                                    <td colspan="2" class="px-6 py-4 text-center text-gray-400 bg-gray-50 text-xs italic">Oculto</td>
                                                @endhasanyrole
                                            @elseif($nombrePlantilla == 'Pets')
                                                @hasanyrole('Vendedor|Administrador|Supervisor')
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                                        {{ number_format((float)($campos['cantidad_millar'] ?? 0), 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                                        {{ number_format((float)($cantidadFinal * (float)($campos['cantidad_millar'] ?? 0)), 2) }}
                                                    </td>
                                                @else
                                                    <td colspan="2" class="px-6 py-4 text-center text-gray-400 bg-gray-50 text-xs italic">Oculto</td>
                                                @endhasanyrole
                                            @endif

                                            @if($showAjuste)
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        name="items[{{ $item->id }}][cantidad]" 
                                                        x-model="ajuste" 
                                                        min="0"
                                                        class="border rounded w-full py-1 px-2 focus:ring-red-500 focus:border-red-500 text-sm font-bold text-orange-600"
                                                        required
                                                    >
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center">
                                                    <span :class="(cantOriginal - (parseFloat(ajuste) || 0)) < 0 ? 'text-red-600' : 'text-gray-600'" 
                                                          x-text="(cantOriginal - (parseFloat(ajuste) || 0)).toFixed(2)">
                                                    </span>
                                                </td>
                                            @endif

                                            @hasanyrole('Vendedor|Administrador|Supervisor')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                                                    {{ $simboloMoneda }}{{ number_format((float)($item->precio_unitario ?? 0), 4) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                                    {{ $simboloMoneda }}{{ number_format((float)$precioTotalFila, 2) }}
                                                </td>
                                            @else
                                                <td class="px-6 py-4 text-center text-gray-400 bg-gray-50 text-xs italic">Oculto</td>
                                                <td class="px-6 py-4 text-center text-gray-400 bg-gray-50 text-xs italic">Oculto</td>
                                            @endhasanyrole
                                        </tr>
                                    @endforeach
                                </tbody>
                                @unlessrole('Logistico')
                                    <tfoot>
                                        @php 
                                            $plantilla = $pedido->cotizacion->plantilla->nombre;
                                            $colSpanLabel = in_array($plantilla, ['Tratadas', 'Pets', 'Bolsas de Polipropileno', 'Bolsas de Polipropileno por kilos']) ? 5 : 3;
                                            if (auth()->user()->hasAnyRole(['Logistico', 'Supervisor', 'Administrador']) && in_array($pedido->estado, ['Pendiente', 'En Revisión'])) {
                                                $colSpanLabel += 2;
                                            }
                                            $subtotalNeto = $sumatoriaSubtotal / 1.18;
                                            $igvDeducido = $sumatoriaSubtotal - $subtotalNeto;
                                        @endphp
                                        <tr class="bg-gray-50">
                                            <td colspan="{{ $colSpanLabel }}" class="px-6 py-4 text-right text-sm font-bold text-gray-700 uppercase">Subtotal</td>
                                            <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 text-right">{{ $simboloMoneda }}{{ number_format((float)$subtotalNeto, 2) }}</td>
                                        </tr>
                                        <tr class="bg-gray-50 border-t border-gray-200">
                                            <td colspan="{{ $colSpanLabel }}" class="px-6 py-4 text-right text-sm font-bold text-gray-700 uppercase">IGV (18%)</td>
                                            <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 text-right">{{ $simboloMoneda }}{{ number_format((float)$igvDeducido, 2) }}</td>
                                        </tr>
                                        <tr class="bg-fenix-green/10 border-t-2 border-fenix-green">
                                            <td colspan="{{ $colSpanLabel }}" class="px-6 py-4 text-right text-base font-black text-gray-900 uppercase">Total Final</td>
                                            <td class="px-6 py-4 text-right text-base font-black text-fenix-green text-right">{{ $simboloMoneda }}{{ number_format((float)$sumatoriaSubtotal, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                @endunlessrole
                            </table>
                        </div>

                        <div class="mt-10 flex flex-col md:flex-row justify-end items-center gap-4 p-6 bg-gray-50 rounded-b-lg border-t border-gray-200">
                            @if(auth()->user()->hasAnyRole(['Logistico', 'Supervisor', 'Administrador']) && in_array($pedido->estado, ['Pendiente', 'En Revisión']))

                                {{-- Fecha de Despacho --}}
                                <div class="flex items-center space-x-2 w-full md:w-auto">
                                    <label for="fecha_entrega_confirmada" class="text-sm font-bold text-gray-700 whitespace-nowrap">
                                        Fecha de Despacho *:
                                    </label>
                                    <input type="date" name="fecha_entrega_confirmada" id="fecha_entrega_confirmada"
                                        value="{{ old('fecha_entrega_confirmada', $pedido->fecha_entrega_confirmada ? $pedido->fecha_entrega_confirmada->format('Y-m-d') : ($pedido->cotizacion->fecha_entrega_estimada ? \Carbon\Carbon::parse($pedido->cotizacion->fecha_entrega_estimada)->format('Y-m-d') : '')) }}"
                                        class="text-sm border-gray-300 focus:ring-green-500 focus:border-green-500 rounded-md shadow-sm font-bold text-gray-700"
                                        required>
                                </div>

                                {{-- Estado de Producción --}}
                                <div class="flex items-center space-x-2 w-full md:w-auto">
                                    <label for="estado_produccion" class="text-sm font-bold text-gray-700 whitespace-nowrap">
                                        Estado de Producción:
                                    </label>
                                    <select name="estado_produccion" id="estado_produccion"
                                        class="text-sm border-gray-300 focus:ring-green-500 focus:border-green-500 rounded-md shadow-sm font-bold text-gray-700 bg-white">
                                        <option value="">-- Sin especificar --</option>
                                        @foreach(\App\Models\Pedido::ESTADOS_PRODUCCION as $ep)
                                            <option value="{{ $ep }}"
                                                {{ old('estado_produccion', $pedido->estado_produccion) === $ep ? 'selected' : '' }}>
                                                {{ $ep }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Botón Confirmar --}}
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform transition hover:scale-105 duration-150">
                                    Confirmar / Guardar
                                </button>

                            @endif
                        </div>
                    </form>


                </div>
            </div>
        </div>

        @include('components.cotizacion.modal-perdida-item')
    </div>

    <script>
        function pedidoDetalle() {
            return {
                modalPerdidaOpen: false,
                perdidaIndex: null,
                perdidaData: {
                    proveedor_nombre: '',
                    motivo_perdida: '',
                    precio_ofrecido: '',
                    entrega_proveedor: '',
                    entrega_nuestra: '',
                    detalle_perdida: ''
                },
                abrirModalPerdida() {
                    this.modalPerdidaOpen = true;
                },
                cerrarModalPerdida() {
                    this.modalPerdidaOpen = false;
                },
                removeItem(index) {},
                confirmarPerdida() {
                    if (!this.perdidaData.motivo_perdida) {
                        alert('Debe ingresar un motivo de pérdida.');
                        return;
                    }
                    if (confirm('¿Confirmar la cancelación de este saldo pendiente y registrar la pérdida?')) {
                        document.getElementById('form-cancelar-saldo').submit();
                    }
                }
            }
        }
    </script>
</x-app-layout>