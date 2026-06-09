<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard de Supervisión Comercial') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Alertas de Carga Masiva (Éxito y Fallos) -->
            @if(session('success'))
                <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">✅</span>
                        <div>
                            <p class="font-bold text-base">Carga Completada</p>
                            <p class="text-sm mt-1">{!! session('success') !!}</p>
                            @if(session('failures') && count(session('failures')))
                                <div class="mt-3 rounded-xl bg-white/70 p-3 text-xs text-slate-700 border border-emerald-100">
                                    <p class="font-semibold text-rose-600 mb-1">⚠️ Registros no procesados debido a errores:</p>
                                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                                        @foreach(session('failures') as $failure)
                                            <li>{{ $failure }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-8 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-900 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">❌</span>
                        <div>
                            <p class="font-bold text-base">Error en la Carga Masiva</p>
                            <ul class="mt-2 list-disc list-inside text-sm space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @hasrole('Supervisor|Administrador')
            <!-- Carga de Chart.js para los Gráficos de Analítica -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <!-- PANEL DE FILTROS SUPERIORES (Fijo y Global) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 mb-8">
                <form method="GET" action="{{ route('dashboard') }}" id="filterForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-end">
                    <input type="hidden" name="moneda" id="hidden_moneda" value="{{ request('moneda', 'soles') }}">
                    
                    <!-- Rango de Fechas (Presets) -->
                    <div>
                        <label for="date_preset" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Periodo Comercial</label>
                        <select name="date_preset" id="date_preset" onchange="handlePresetChange(this.value)"
                            class="w-full rounded-xl border-slate-200 text-sm focus:ring-fenix-green focus:border-fenix-green p-3 bg-slate-50 text-slate-700 font-medium">
                            <option value="hoy" {{ ($preset ?? '') == 'hoy' ? 'selected' : '' }}>Hoy</option>
                            <option value="esta_semana" {{ ($preset ?? '') == 'esta_semana' ? 'selected' : '' }}>Esta Semana</option>
                            <option value="este_mes" {{ ($preset ?? '') == 'este_mes' ? 'selected' : '' }}>Este Mes</option>
                            <option value="anio_2026" {{ ($preset ?? '') == 'anio_2026' ? 'selected' : '' }}>Año 2026</option>
                            <option value="personalizado" {{ ($preset ?? '') == 'personalizado' ? 'selected' : '' }}>Rango Personalizado</option>
                        </select>
                    </div>

                    <!-- Inputs de Fechas -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Fecha Inicio</label>
                            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                                class="w-full rounded-xl border-slate-200 text-sm focus:ring-fenix-green focus:border-fenix-green p-3 bg-slate-50 text-slate-700 font-medium">
                        </div>
                        <div>
                            <label for="end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Fecha Fin</label>
                            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                                class="w-full rounded-xl border-slate-200 text-sm focus:ring-fenix-green focus:border-fenix-green p-3 bg-slate-50 text-slate-700 font-medium">
                        </div>
                    </div>

                    <!-- Alternar Moneda Estético -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Moneda del Dashboard</label>
                        <div class="flex items-center bg-slate-100 p-1 rounded-xl h-[46px]">
                            <button type="button" onclick="setCurrency('soles')" id="btn_currency_soles"
                                class="flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-center">
                                Soles (S/)
                            </button>
                            <button type="button" onclick="setCurrency('dolares')" id="btn_currency_dolares"
                                class="flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-center">
                                Dólares ($)
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SECCIÓN: ANALÍTICA COMERCIAL AVANZADA -->
            <div class="mb-8" x-data="commercialAnalytics({{ json_encode($analiticaData ?? []) }})" x-cloak>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-6 lg:p-8">
                    <!-- Cabecera de la Sección -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-6 mb-6">
                        <div>
                            <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                                <span class="text-emerald-500">📊</span> Analítica Comercial Avanzada
                            </h3>
                            <p class="text-xs text-slate-450 mt-1">Monitoreo interactivo de ventas y productos líderes agrupados por línea de producción.</p>
                        </div>
                        <div class="mt-3 md:mt-0 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-450 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-550"></span>
                            </span>
                            Datos Actualizados
                        </div>
                    </div>

                    <!-- Estado Vacío -->
                    <div x-show="rawLines.length === 0" class="text-center py-12 text-slate-450 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <span class="text-4xl block mb-2">📭</span>
                        No se registraron ventas en el periodo seleccionado para generar la Analítica Comercial Avanzada.
                    </div>

                    <div x-show="rawLines.length > 0" class="space-y-8">
                        <!-- BLOQUE SUPERIOR: Dona + Tabla de Líneas -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                            <!-- Gráfico de Dona -->
                            <div class="lg:col-span-5 bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col justify-between">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 text-center">Líneas Generales de Venta</h4>
                                <div class="relative w-full h-[280px] flex items-center justify-center">
                                    <canvas id="lineasDoughnutChart"></canvas>
                                </div>
                                <p class="text-[10px] text-center text-slate-450 mt-4 italic">Haz clic en una tajada de la dona para filtrar las sublíneas y productos de abajo.</p>
                            </div>

                            <!-- Tabla de Ingresos por Línea -->
                            <div class="lg:col-span-7 flex flex-col justify-between">
                                <div class="overflow-x-auto">
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex justify-between items-center">
                                        <span>Top de Ingresos por Línea</span>
                                        <span class="text-xs font-semibold text-slate-400 normal-case">Línea seleccionada: <strong class="text-slate-700 bg-slate-100 px-2.5 py-1 rounded" x-text="selectedLine"></strong></span>
                                    </h4>
                                    <table class="w-full text-sm text-left text-slate-500">
                                        <thead class="text-xs text-slate-400 uppercase bg-slate-50 rounded-lg">
                                            <tr>
                                                <th scope="col" class="px-4 py-3">Línea</th>
                                                <th scope="col" class="px-4 py-3 text-right">Total Facturado</th>
                                                <th scope="col" class="px-4 py-3 text-right">% Participación</th>
                                                <th scope="col" class="px-4 py-3 text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="item in linesList" :key="item.linea">
                                                <tr class="bg-white border-b hover:bg-slate-50 transition-colors"
                                                    :class="{'bg-emerald-50/40 font-semibold border-l-4 border-emerald-555': selectedLine === item.linea}">
                                                    <td class="px-4 py-3.5 text-slate-800 font-medium" x-text="item.linea"></td>
                                                    <td class="px-4 py-3.5 text-right font-extrabold text-slate-700">
                                                        <span x-text="currency === 'soles' ? 'S/. ' : 'US$ '"></span>
                                                        <span x-text="parseFloat(item[currency]).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                                    </td>
                                                    <td class="px-4 py-3.5 text-right font-semibold text-slate-600">
                                                        <span x-text="totalSalesOfAllLines > 0 ? round((item[currency] / totalSalesOfAllLines) * 100, 1) + '%' : '0%'"></span>
                                                    </td>
                                                    <td class="px-4 py-3.5 text-center">
                                                        <button type="button" @click="selectLine(item.linea)"
                                                                class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg transition">
                                                            🎯 Filtrar
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <hr class="border-slate-100 my-2">

                        <!-- BLOQUE MEDIO: Gráfico de Barras Horizontales (Sublíneas) -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex justify-between items-center">
                                <span>Rendimiento por Sublínea (Comparativa Periodo)</span>
                                <span class="text-xs text-slate-450 normal-case">Sublíneas de: <strong class="text-emerald-600 font-bold" x-text="selectedLine"></strong></span>
                            </h4>
                            
                            <div class="relative w-full h-[240px] flex items-center justify-center">
                                <canvas id="sublineasBarChart"></canvas>
                            </div>
                        </div>

                        <hr class="border-slate-100 my-2">

                        <!-- BLOQUE INFERIOR: Top 10 de Productos Estrella -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex justify-between items-center">
                                <span>⭐ Top 10 Productos Estrella</span>
                                <span class="text-xs font-semibold text-slate-400 normal-case">Línea seleccionada: <strong class="text-slate-700 bg-slate-100 px-2.5 py-1 rounded" x-text="selectedLine"></strong></span>
                            </h4>

                            <div x-show="currentTopProducts.length === 0" class="text-center py-6 text-slate-400 bg-slate-50 rounded-xl">
                                No se encontraron productos estrella vendidos para la línea seleccionada.
                            </div>

                            <div x-show="currentTopProducts.length > 0" class="overflow-x-auto border border-slate-100 rounded-xl">
                                <table class="w-full text-sm text-left text-slate-500">
                                    <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                                        <tr>
                                            <th scope="col" class="px-5 py-3">Código</th>
                                            <th scope="col" class="px-5 py-3">Producto</th>
                                            <th scope="col" class="px-5 py-3 text-right">Cantidad Vendida</th>
                                            <th scope="col" class="px-5 py-3 text-center">U/M</th>
                                            <th scope="col" class="px-5 py-3 text-right">Total Facturado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="p in currentTopProducts" :key="p.producto_id">
                                            <tr class="bg-white border-b hover:bg-slate-50/50 transition-colors">
                                                <td class="px-5 py-4 font-semibold text-slate-800" x-text="p.codigo"></td>
                                                <td class="px-5 py-4 text-slate-700 font-medium" x-text="p.nombre"></td>
                                                <td class="px-5 py-4 text-right font-extrabold text-slate-800" x-text="parseFloat(p.cantidad_vendida).toLocaleString('es-PE', { minimumFractionDigits: 3, maximumFractionDigits: 3 })"></td>
                                                <td class="px-5 py-4 text-center text-slate-450 font-bold" x-text="p.unidad_medida"></td>
                                                <td class="px-5 py-4 text-right font-extrabold text-emerald-600 bg-emerald-50/20">
                                                    <span x-text="currency === 'soles' ? 'S/. ' : 'US$ '"></span>
                                                    <span x-text="parseFloat(p[currency]).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
                                        <span class="font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">{{ number_format($ruptura->stock_deficit ?? $ruptura->stock, 2) }}</span>
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

            @if(auth()->user()->hasRole('Administrador'))
                <!-- SECCIÓN: CARGAS MASIVAS (Solo Administradores) -->
                <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-150 p-6 lg:p-8">
                    <div class="border-b border-slate-100 pb-4 mb-6">
                        <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                            <span>📥</span> Carga Masiva de Datos
                        </h3>
                        <p class="text-xs text-slate-450 mt-1">Carga archivos Excel (.xlsx, .xls) o CSV con los formatos correspondientes para importar masivamente Clientes o Contactos.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Carga de Clientes -->
                        <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-bold text-slate-700 text-base">Clientes</h4>
                                    <a href="{{ route('importacion.template', 'clientes') }}" 
                                       class="text-xs font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg transition">
                                        📄 Descargar Plantilla
                                    </a>
                                </div>
                                <p class="text-xs text-slate-450 mb-4">
                                    Campos requeridos: <strong class="text-slate-600 font-mono">ruc, razon_social, condicion_pago</strong>.
                                </p>
                            </div>
                            <form action="{{ route('importacion.clientes') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                </div>
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center rounded-xl bg-fenix-green px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-green-700 transition">
                                    ⚡ Importar Clientes
                                </button>
                            </form>
                        </div>

                        <!-- Carga de Contactos -->
                        <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-bold text-slate-700 text-base">Contactos</h4>
                                    <a href="{{ route('importacion.template', 'contactos') }}" 
                                       class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg transition">
                                        📄 Descargar Plantilla
                                    </a>
                                </div>
                                <p class="text-xs text-slate-450 mb-4">
                                    Campos requeridos: <strong class="text-slate-600 font-mono">nombre_completo</strong>.
                                </p>
                            </div>
                            <form action="{{ route('importacion.contactos') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                </div>
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-blue-700 transition">
                                    ⚡ Importar Contactos
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Scripts de Alpine.js para Dashboard -->
            <script>
                // Funciones globales para manejo de Filtros y Moneda
                function handlePresetChange(value) {
                    const today = new Date();
                    let startDate, endDate;
                    
                    if (value === 'hoy') {
                        startDate = formatDate(today);
                        endDate = formatDate(today);
                    } else if (value === 'esta_semana') {
                        const day = today.getDay();
                        const diffToMonday = today.getDate() - day + (day === 0 ? -6 : 1);
                        const monday = new Date(new Date().setDate(diffToMonday));
                        const sunday = new Date(monday);
                        sunday.setDate(monday.getDate() + 6);
                        startDate = formatDate(monday);
                        endDate = formatDate(sunday);
                    } else if (value === 'este_mes') {
                        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        startDate = formatDate(firstDay);
                        endDate = formatDate(lastDay);
                    } else if (value === 'anio_2026') {
                        startDate = '2026-01-01';
                        endDate = '2026-12-31';
                    } else if (value === 'personalizado') {
                        return;
                    }
                    
                    document.getElementById('start_date').value = startDate;
                    document.getElementById('end_date').value = endDate;
                    document.getElementById('filterForm').submit();
                }

                function formatDate(date) {
                    const d = new Date(date);
                    let month = '' + (d.getMonth() + 1);
                    let day = '' + d.getDate();
                    const year = d.getFullYear();

                    if (month.length < 2) month = '0' + month;
                    if (day.length < 2) day = '0' + day;

                    return [year, month, day].join('-');
                }

                function setCurrency(curr) {
                    document.getElementById('hidden_moneda').value = curr;
                    
                    const btnSoles = document.getElementById('btn_currency_soles');
                    const btnDolares = document.getElementById('btn_currency_dolares');
                    
                    if (curr === 'soles') {
                        btnSoles.className = "flex-1 py-2 px-3 rounded-lg text-sm font-bold transition-all duration-200 text-center bg-white text-slate-800 shadow-sm border border-slate-200";
                        btnDolares.className = "flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-center text-slate-500 hover:text-slate-700";
                    } else {
                        btnDolares.className = "flex-1 py-2 px-3 rounded-lg text-sm font-bold transition-all duration-200 text-center bg-white text-slate-800 shadow-sm border border-slate-200";
                        btnSoles.className = "flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-center text-slate-500 hover:text-slate-700";
                    }
                    
                    window.dispatchEvent(new CustomEvent('currency-changed', { detail: curr }));
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const initialCurrency = "{{ request('moneda', 'soles') }}";
                    setCurrency(initialCurrency);
                    
                    const presetSelect = document.getElementById('date_preset');
                    const startDateInput = document.getElementById('start_date');
                    const endDateInput = document.getElementById('end_date');
                    
                    const submitOnChange = () => {
                        if (presetSelect.value === 'personalizado') {
                            document.getElementById('filterForm').submit();
                        }
                    };
                    startDateInput.addEventListener('change', submitOnChange);
                    endDateInput.addEventListener('change', submitOnChange);
                });

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

                function commercialAnalytics(initialData) {
                    return {
                        currency: "{{ request('moneda', 'soles') }}",
                        selectedLine: '',
                        rawLines: initialData.lineas || [],
                        rawSublines: initialData.sublineas || {},
                        rawProducts: initialData.productos || [],
                        doughnutChart: null,
                        barChart: null,
                        
                        init() {
                            this.rawLines.sort((a, b) => b[this.currency] - a[this.currency]);
                            if (this.rawLines.length > 0) {
                                this.selectedLine = this.rawLines[0].linea;
                            }
                            
                            window.addEventListener('currency-changed', (e) => {
                                this.currency = e.detail;
                                this.updateAll();
                            });
                            
                            this.$nextTick(() => {
                                this.initCharts();
                            });
                        },
                        
                        get linesList() {
                            return [...this.rawLines].sort((a, b) => b[this.currency] - a[this.currency]);
                        },
                        
                        get totalSalesOfAllLines() {
                            return this.rawLines.reduce((sum, item) => sum + parseFloat(item[this.currency] || 0), 0);
                        },
                        
                        get currentSublines() {
                            if (!this.selectedLine || !this.rawSublines[this.selectedLine]) return [];
                            return [...this.rawSublines[this.selectedLine]].sort((a, b) => b[this.currency] - a[this.currency]);
                        },
                        
                        get currentTopProducts() {
                            if (!this.selectedLine) return [];
                            return [...this.rawProducts]
                                    .filter(p => p.linea === this.selectedLine)
                                    .sort((a, b) => b.cantidad_vendida - a.cantidad_vendida)
                                    .slice(0, 10);
                        },
                        
                        selectLine(lineName) {
                            this.selectedLine = lineName;
                            this.updateBarChart();
                        },
                        
                        updateAll() {
                            this.updateDoughnutChart();
                            this.updateBarChart();
                        },
                        
                        round(val, dec) {
                            if (isNaN(val)) return 0;
                            return Number(Math.round(val+'e'+dec)+'e-'+dec);
                        },
                        
                        initCharts() {
                            const ctxDoughnut = document.getElementById('lineasDoughnutChart').getContext('2d');
                            const ctxBar = document.getElementById('sublineasBarChart').getContext('2d');
                            
                            const lineNames = this.linesList.map(l => l.linea);
                            const lineValues = this.linesList.map(l => l[this.currency]);
                            
                            const colors = [
                                '#0CC954', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444', 
                                '#EC4899', '#10B981', '#6366F1', '#14B8A6', '#F43F5E'
                            ];
                            
                            this.doughnutChart = new Chart(ctxDoughnut, {
                                type: 'doughnut',
                                data: {
                                    labels: lineNames,
                                    datasets: [{
                                        data: lineValues,
                                        backgroundColor: colors.slice(0, lineNames.length),
                                        borderWidth: 2,
                                        hoverOffset: 12
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => {
                                                    const value = context.raw;
                                                    const symbol = this.currency === 'soles' ? 'S/. ' : 'US$ ';
                                                    return `${context.label}: ${symbol}${parseFloat(value).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                                }
                                            }
                                        }
                                    },
                                    onClick: (event, elements) => {
                                        if (elements && elements.length > 0) {
                                            const index = elements[0].index;
                                            const clickedLine = this.doughnutChart.data.labels[index];
                                            this.selectLine(clickedLine);
                                        }
                                    }
                                }
                            });
                            
                            const sublineNames = this.currentSublines.map(s => s.sublinea);
                            const sublineValues = this.currentSublines.map(s => s[this.currency]);
                            
                            this.barChart = new Chart(ctxBar, {
                                type: 'bar',
                                data: {
                                    labels: sublineNames,
                                    datasets: [{
                                        label: 'Ventas por Sublínea',
                                        data: sublineValues,
                                        backgroundColor: '#3B82F6',
                                        borderRadius: 8,
                                        maxBarThickness: 32
                                    }]
                                },
                                options: {
                                    indexAxis: 'y',
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: (context) => {
                                                    const value = context.raw;
                                                    const symbol = this.currency === 'soles' ? 'S/. ' : 'US$ ';
                                                    return `${context.label}: ${symbol}${parseFloat(value).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: {
                                                display: false
                                            },
                                            ticks: {
                                                callback: (value) => {
                                                    const symbol = this.currency === 'soles' ? 'S/.' : '$';
                                                    return `${symbol} ${value}`;
                                                }
                                            }
                                        },
                                        y: {
                                            grid: {
                                                display: false
                                            }
                                        }
                                    }
                                }
                            });
                        },
                        
                        updateDoughnutChart() {
                            if (!this.doughnutChart) return;
                            const lineNames = this.linesList.map(l => l.linea);
                            const lineValues = this.linesList.map(l => l[this.currency]);
                            
                            this.doughnutChart.data.labels = lineNames;
                            this.doughnutChart.data.datasets[0].data = lineValues;
                            this.doughnutChart.update();
                        },
                        
                        updateBarChart() {
                            if (!this.barChart) return;
                            const sublineNames = this.currentSublines.map(s => s.sublinea);
                            const sublineValues = this.currentSublines.map(s => s[this.currency]);
                            
                            this.barChart.data.labels = sublineNames;
                            this.barChart.data.datasets[0].data = sublineValues;
                            this.barChart.update();
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
