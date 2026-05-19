<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-800">Bienvenid@, {{ auth()->user()->name }}</h3>
                        <p class="text-gray-600 mt-1">Rol: <span class="font-bold text-fenix-green">{{ auth()->user()->roles->pluck('name')->join(', ') }}</span></p>
                    </div>
                    @if(auth()->user()->hasRole('Administrador'))
                        <a href="{{ route('importacion.index') }}" class="inline-flex items-center justify-center rounded-full bg-fenix-green px-5 py-3 text-sm font-semibold text-white shadow hover:bg-green-700 transition">
                            Importación Masiva
                        </a>
                    @endif
                </div>
            </div>

            @if(!$data['is_admin'])
                <!-- PANEL VENDEDOR -->
                <!-- KPIs Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Ventas del Mes en Soles -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-fenix-green to-green-700 px-6 py-4">
                            <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Ventas del Mes en Soles</p>
                        </div>
                        <div class="px-6 py-6">
                            <h4 class="text-4xl font-bold text-fenix-green">S/. {{ number_format($ventasSoles ?? 0, 2) }}</h4>
                            <p class="text-gray-500 text-sm mt-2">💰 Total acumulado PEN</p>
                        </div>
                    </div>

                    <!-- Cotizaciones del Mes (Pedidos Mes) -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-fenix-gold to-yellow-600 px-6 py-4">
                            <p class="text-amber-900 text-sm font-semibold uppercase tracking-wide">Pedidos Mes</p>
                        </div>
                        <div class="px-6 py-6">
                            <h4 class="text-4xl font-bold text-fenix-gold">{{ $data['cantidad_pedidos_mes'] ?? 0 }}</h4>
                            <p class="text-gray-500 text-sm mt-2">📦 Ordenes realizadas</p>
                        </div>
                    </div>

                    <!-- Ventas del Mes en Dólares -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                            <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Ventas del Mes en Dólares</p>
                        </div>
                        <div class="px-6 py-6">
                            <h4 class="text-4xl font-bold text-indigo-600">$ {{ number_format($ventasDolares ?? 0, 2) }}</h4>
                            <p class="text-gray-500 text-sm mt-2">💵 Total acumulado USD</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content - Two Columns -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Column 1: Últimas Cotizaciones -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-fenix-green to-green-700 px-6 py-4">
                            <h4 class="text-white text-lg font-bold flex items-center gap-2">
                                Mis Últimos Pedidos
                            </h4>
                        </div>
                        <div class="overflow-x-auto">
                            @if($data['ultimos_pedidos']->isNotEmpty())
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 border-b-2 border-gray-200">
                                            <th class="px-6 py-3 text-left text-gray-700 font-semibold">Pedido</th>
                                            <th class="px-6 py-3 text-left text-gray-700 font-semibold">Cliente</th>
                                            <th class="px-6 py-3 text-left text-gray-700 font-semibold">Fecha Despacho</th>
                                            <th class="px-6 py-3 text-left text-gray-700 font-semibold">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['ultimos_pedidos'] as $pedido)
                                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4">
                                                     <a href="{{ route('pedidos.show', $pedido->numero ?? $pedido->id) }}" class="text-fenix-green font-semibold hover:underline">
                                                         {{ $pedido->numero ?? '#'.$pedido->cotizacion_id }}
                                                     </a>
                                                 </td>
                                                <td class="px-6 py-4 text-gray-800">{{ $pedido->cotizacion->cliente->nombre ?? 'N/A' }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="text-fenix-gold font-bold">
                                                        {{ $pedido->fecha_entrega_confirmada ? \Carbon\Carbon::parse($pedido->fecha_entrega_confirmada)->format('d/m/Y') : 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                                        @if($pedido->estado === 'Entregado' || $pedido->estado === 'Despachado') bg-green-100 text-green-800
                                                        @elseif($pedido->estado === 'Cancelado' || $pedido->estado === 'Anulado') bg-red-100 text-red-800
                                                        @elseif($pedido->estado === 'Pendiente') bg-yellow-100 text-yellow-800
                                                        @elseif($pedido->estado === 'En Proceso' || $pedido->estado === 'Aprobado') bg-blue-100 text-blue-800
                                                        @else bg-gray-100 text-gray-800 @endif
                                                    ">
                                                        {{ $pedido->estado }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="px-6 py-12 text-center">
                                    <p class="text-gray-500 text-lg">📭 No tienes pedidos aún</p>
                                    <a href="{{ route('cotizaciones.index') }}" class="mt-4 inline-block px-4 py-2 bg-fenix-green text-white rounded-lg hover:bg-green-700 transition">
                                        Crear Cotización
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Column 2: Clientes VIP -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-fenix-gold to-yellow-600 px-6 py-4">
                            <h4 class="text-white text-lg font-bold flex items-center gap-2">
                                👑 Mis Clientes VIP
                            </h4>
                        </div>
                        <div>
                            @if($data['clientes_vip']->isNotEmpty())
                                <div class="divide-y divide-gray-200">
                                    @foreach($data['clientes_vip'] as $clienteVip)
                                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                            <div class="flex-1">
                                                <h5 class="font-semibold text-gray-800 text-lg">
                                                    {{ $clienteVip->cliente->nombre ?? 'N/A' }}
                                                </h5>
                                                <div class="flex flex-col gap-1 mt-1">
                                                    <span class="text-sm text-gray-600">RUC: {{ $clienteVip->cliente->ruc ?? 'Sin RUC' }}</span>
                                                    <span class="text-xs font-bold text-fenix-green flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        S/. {{ number_format($clienteVip->total_monto, 2) }} <span class="font-normal text-gray-400 font-sans tracking-normal opacity-70">(Inversión Mes)</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="bg-fenix-gold bg-opacity-20 px-4 py-2 rounded-lg border border-fenix-gold border-opacity-30">
                                                    <p class="text-fenix-gold font-black text-xl leading-none">{{ $clienteVip->total_pedidos }}</p>
                                                    <p class="text-[10px] text-fenix-gold font-bold uppercase mt-1">Pedidos</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="px-6 py-12 text-center">
                                    <p class="text-gray-500 text-lg font-medium">🤝 Sin clientes VIP en este mes</p>
                                    <p class="text-gray-400 text-sm mt-1">Sigue convirtiendo cotizaciones para liderar el ranking</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Productos Top (Opcional) -->
                @if($data['productos_top']->isNotEmpty())
                <div class="mt-8 bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4">
                        <h4 class="text-white text-lg font-bold flex items-center gap-2">
                            ⭐ Productos Más Vendidos
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b-2 border-gray-200">
                                    <th class="px-6 py-3 text-left text-gray-700 font-semibold">Producto</th>
                                    <th class="px-6 py-3 text-left text-gray-700 font-semibold text-center">Cant. en Cotizaciones</th>
                                    <th class="px-6 py-3 text-left text-gray-700 font-semibold text-center">Cant. en Pedidos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['productos_top'] as $index => $producto)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-semibold text-gray-800">
                                            <div class="flex items-center gap-3">
                                                <span class="flex items-center justify-center bg-purple-100 text-purple-700 w-6 h-6 rounded text-[10px] font-bold">#{{ $index+1 }}</span>
                                                {{ $producto->nombre ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-gray-600 font-medium italic">
                                            {{ number_format($producto->total_cotizado, 2) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="flex-1 bg-gray-100 rounded-full h-3 shadow-inner">
                                                    <div class="bg-gradient-to-r from-purple-500 to-purple-700 h-3 rounded-full transition-all duration-500" 
                                                         style="width: {{ ($producto->total_vendido / ($data['productos_top']->first()->total_vendido ?: 1)) * 100 }}%">
                                                    </div>
                                                </div>
                                                <span class="text-purple-700 font-black text-sm min-w-[70px] text-right">
                                                    {{ number_format($producto->total_vendido, 2) }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            @else
                <!-- PANEL ADMINISTRADOR/SUPERVISOR -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-fenix-green to-green-700 text-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Pedidos Realizados</p>
                        <h4 class="text-4xl font-bold mt-2">{{ $data['total_pedidos'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-gradient-to-br from-fenix-gold to-yellow-600 text-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <p class="text-yellow-900 text-sm font-semibold uppercase tracking-wide">Cotizaciones Emitidas</p>
                        <h4 class="text-4xl font-bold mt-2">{{ $data['total_cotizaciones'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-gradient-to-br from-purple-600 to-purple-800 text-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <p class="text-purple-200 text-sm font-semibold uppercase tracking-wide">Producto Más Popular</p>
                        <h4 class="text-2xl font-bold mt-2">{{ $data['top_producto'] ?? 'N/A' }}</h4>
                    </div>
                </div>

                <!-- Vendedor Top -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h4 class="font-bold text-gray-800 mb-6 text-xl flex items-center gap-2">🏆 Vendedor Con Mayor Performance</h4>
                    <div class="text-center">
                        <div class="inline-block w-32 h-32 bg-gradient-to-br from-fenix-green to-green-700 rounded-full flex items-center justify-center text-6xl mb-6 shadow-lg">
                            👤
                        </div>
                        <h5 class="text-2xl font-bold text-gray-800">{{ $data['top_vendedor'] ?? 'Sin datos' }}</h5>
                        <p class="text-gray-600 mt-2 text-lg"><span class="font-bold text-fenix-gold">{{ $data['top_vendedor_pedidos'] ?? 0 }}</span> Pedidos cerrados</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>