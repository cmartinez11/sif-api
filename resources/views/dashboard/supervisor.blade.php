<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard de Supervisión Comercial') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @hasrole('Supervisor|Administrador')
            <!-- Filtro de Rango de Fechas -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 mb-8">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Fecha Inicio</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                                class="w-full rounded-xl border-slate-200 text-sm focus:ring-fenix-green focus:border-fenix-green p-3 bg-slate-50">
                        </div>
                        <div>
                            <label for="end_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Fecha Fin</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                                class="w-full rounded-xl border-slate-200 text-sm focus:ring-fenix-green focus:border-fenix-green p-3 bg-slate-50">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-fenix-green px-5 py-3 text-sm font-semibold text-white shadow hover:bg-green-700 transition">
                            🔍 Filtrar Periodo
                        </button>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition">
                            🔄 Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- TARJETAS KPI GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- CARD 1: Venta Total Periodo -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ventas Aprobadas</p>
                        <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-xs">S/ & $</span>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div>
                            <span class="text-[10px] font-semibold text-slate-450 block uppercase tracking-wider">Acumulado Soles</span>
                            <h3 class="text-2xl font-extrabold text-emerald-650 leading-none">
                                S/. {{ number_format($totalVentasPeriodoSoles, 2) }}
                            </h3>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-[10px] font-semibold text-slate-450 block uppercase tracking-wider">Acumulado Dólares</span>
                            <h3 class="text-2xl font-extrabold text-indigo-600 leading-none">
                                US$ {{ number_format($totalVentasPeriodoDolares, 2) }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Eficiencia de Conversión -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Conversión Comercial</p>
                        <span class="p-2.5 rounded-xl bg-blue-50 text-blue-600">📈</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-extrabold text-slate-800 leading-none">
                            {{ $eficienciaConversion }}%
                        </h3>
                        <p class="text-xs text-slate-500 mt-2">
                            {{ $convertidas }} de {{ $totalCotizaciones }} cotizaciones cerradas
                        </p>
                    </div>
                </div>

                <!-- CARD 3: Cola de Producción -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cola de Producción</p>
                        <span class="p-2.5 rounded-xl bg-amber-50 text-amber-600">🏭</span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-3xl font-extrabold text-slate-800 leading-none">
                            {{ $pedidosPorProducir }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-2">Pedidos pendientes por fabricar</p>
                    </div>
                </div>

                <!-- CARD 4: Alertas de Ruptura (Saldos Negativos) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alertas de Ruptura</p>
                        <span class="p-2.5 rounded-xl bg-red-50 text-rose-600">⚠️</span>
                    </div>
                    <div class="mt-4">
                        @if($alertasRuptura->isNotEmpty())
                            <div class="space-y-1">
                                @foreach($alertasRuptura as $ruptura)
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="font-semibold text-slate-600 truncate max-w-[100px]">{{ $ruptura->codigo }}</span>
                                        <span class="font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">{{ number_format($ruptura->stock, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <h3 class="text-lg font-bold text-emerald-600">Sin alertas</h3>
                            <p class="text-xs text-slate-500 mt-1">Todos los stocks están al día</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SECCIÓN PRINCIPAL: FILTRADO EN CASCADA -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" x-data="supervisorDashboard()">
                <!-- COMPONENTE A: Ranking de Vendedoras -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6">
                    <div class="mb-6">
                        <h4 class="text-lg font-bold text-slate-850 flex items-center gap-2">
                            <span>🏆</span> Ranking de Vendedoras
                        </h4>
                        <p class="text-xs text-slate-450 mt-1">Vendedoras ordenadas por facturación total en Soles</p>
                    </div>

                    @if(count($rankingVendedoras) > 0)
                        <div class="space-y-4">
                            @php
                                $maxSortValue = max(array_column($rankingVendedoras, 'sort_value')) ?: 1;
                            @endphp
                            @foreach($rankingVendedoras as $index => $ranking)
                                <div class="p-4 rounded-xl border border-slate-100 hover:bg-slate-50 transition cursor-pointer"
                                     @click="selectedVendedora = '{{ $ranking['vendedor_id'] }}'; fetchProductos()">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center bg-fenix-green bg-opacity-10 text-fenix-green w-7 h-7 rounded-lg text-xs font-bold shrink-0">
                                                #{{ $index + 1 }}
                                            </span>
                                            <div>
                                                <p class="font-bold text-sm text-slate-800">{{ $ranking['vendedor_name'] }}</p>
                                                <p class="text-xs text-slate-450">{{ $ranking['cantidad_pedidos'] }} pedidos aprobados</p>
                                            </div>
                                        </div>
                                        <div class="flex sm:flex-col justify-between sm:text-right gap-4 sm:gap-0 mt-1 sm:mt-0 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
                                            <p class="text-xs font-bold text-slate-700">
                                                <span class="text-slate-400 font-normal">S/:</span> S/. {{ number_format($ranking['total_soles'], 2) }}
                                            </p>
                                            <p class="text-xs font-bold text-slate-700 sm:mt-1">
                                                <span class="text-slate-400 font-normal">US$:</span> $ {{ number_format($ranking['total_dolares'], 2) }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Barra de progreso visual -->
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                                        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-1.5 rounded-full" 
                                             style="width: {{ ($ranking['sort_value'] / $maxSortValue) * 100 }}%">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 text-slate-400">
                            <span class="text-3xl block mb-2">📭</span>
                            No se registraron ventas en este periodo.
                        </div>
                    @endif
                </div>

                <!-- COMPONENTE B: Mix de Productos Estrella (Alpine.js) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 flex flex-col justify-between">
                    <div>
                        <div class="mb-6">
                            <h4 class="text-lg font-bold text-slate-850 flex items-center gap-2">
                                <span>⭐</span> Productos Estrella
                            </h4>
                            <p class="text-xs text-slate-450 mt-1">TOP 5 productos más vendidos físicos por vendedora</p>
                        </div>

                        <!-- Selector de Vendedora -->
                        <div class="mb-6">
                            <label for="vendedora_select" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Seleccione Vendedora para filtrar:
                            </label>
                            <select id="vendedora_select" x-model="selectedVendedora" @change="fetchProductos()" 
                                    class="w-full rounded-xl border-slate-200 text-sm focus:ring-fenix-green focus:border-fenix-green p-3 bg-slate-50 text-slate-700">
                                <option value="">-- Seleccione una vendedora --</option>
                                @foreach($vendedoras as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Loader -->
                        <div x-show="loading" class="flex flex-col items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-fenix-green"></div>
                            <span class="text-xs text-slate-400 mt-2">Cargando catálogo estrella...</span>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && productos.length === 0" class="text-center py-12 text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <span class="text-3xl block mb-2">📊</span>
                            Selecciona una vendedora del select o haz clic en su tarjeta en el ranking para ver sus productos estrella.
                        </div>

                        <!-- Tabla de Productos -->
                        <div x-show="!loading && productos.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-500">
                                <thead class="text-xs text-slate-400 uppercase bg-slate-50 rounded-lg">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Código</th>
                                        <th scope="col" class="px-4 py-3">Producto</th>
                                        <th scope="col" class="px-4 py-3 text-right">Cant. Vendida</th>
                                        <th scope="col" class="px-4 py-3 text-right">Ventas S/.</th>
                                        <th scope="col" class="px-4 py-3 text-right">Ventas US$</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="p in productos" :key="p.producto_id">
                                        <tr class="bg-white border-b hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-4 font-semibold text-slate-800" x-text="p.codigo"></td>
                                            <td class="px-4 py-4 text-slate-700" x-text="p.nombre"></td>
                                            <td class="px-4 py-4 text-right font-bold text-slate-700">
                                                <span x-text="parseFloat(p.cantidad_sold || p.cantidad_vendida).toLocaleString('es-PE', { minimumFractionDigits: 3, maximumFractionDigits: 3 })"></span>
                                                <span class="text-[10px] text-slate-400 font-normal ml-1" x-text="p.unidad_medida"></span>
                                            </td>
                                            <td class="px-4 py-4 text-right font-bold text-emerald-650">
                                                <span>S/. </span>
                                                <span x-text="parseFloat(p.monto_soles).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                            </td>
                                            <td class="px-4 py-4 text-right font-bold text-indigo-600">
                                                <span>$ </span>
                                                <span x-text="parseFloat(p.monto_dolares).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scripts de Alpine.js para Dashboard -->
            <script>
                function supervisorDashboard() {
                    return {
                        selectedVendedora: '',
                        productos: [],
                        loading: false,
                        startDate: '{{ $startDate }}',
                        endDate: '{{ $endDate }}',

                        fetchProductos() {
                            if (!this.selectedVendedora) {
                                this.productos = [];
                                return;
                            }
                            this.loading = true;
                            fetch(`/dashboard/supervisor/vendedora-productos?vendedora_id=${this.selectedVendedora}&start_date=${this.startDate}&end_date=${this.endDate}`)
                                .then(res => {
                                    if (!res.ok) throw new Error('Error de servidor');
                                    return res.json();
                                })
                                .then(data => {
                                    this.productos = data;
                                    this.loading = false;
                                })
                                .catch(err => {
                                    console.error('Error fetching vendedora products:', err);
                                    this.loading = false;
                                    this.productos = [];
                                });
                        }
                    };
                }
            </script>
            @else
            <!-- Mensaje de no autorizado si por error entra otro rol como Logistico -->
            <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-8 text-center max-w-xl mx-auto my-12">
                <span class="text-5xl block mb-4">🚫</span>
                <h3 class="text-xl font-bold text-red-600 mb-2">Acceso Denegado</h3>
                <p class="text-slate-500 text-sm">Esta sección contiene datos comerciales restringidos de Plásticos Fénix. No tienes los permisos necesarios para visualizar esta información.</p>
            </div>
            @endhasrole

        </div>
    </div>
</x-app-layout>
