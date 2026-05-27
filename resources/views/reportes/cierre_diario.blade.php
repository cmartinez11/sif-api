<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Reporte de Cierre Diario') }}
        </h2>
    </x-slot>

    <!-- FontAwesome para los iconos de historial -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Script de Alpine.js con compatibilidad PHP 7.3+ (Laravel 8) -->
    <script>
        function cierreDiarioData() {
            return {
                search: '',
                stockFilter: 'all',
                movementFilter: 'all',
                items: {!! $productosReporte->map(function($p) {
                    $stockActual = (float)($p->stock ?? 0);
                    $vendidoHoy = (float)($p->vendido_hoy ?? 0);
                    $deudaArrastrada = (float)($p->deuda_arrastrada ?? 0);
                    $comprometido = (float)($p->stock_comprometido ?? 0);
                    $vendidoHoyFuturo = (float)($p->vendido_hoy_futuro ?? 0);
                    $saldoSif = ($stockActual - $deudaArrastrada) - $comprometido + $vendidoHoyFuturo;
                    return [
                        'codigo' => $p->codigo,
                        'nombre' => $p->nombre, // Directo sin escapes duplicados para que Alpine lo lea correctamente
                        'linea' => $p->linea ?? 'N/A',
                        'unidad' => $p->unidad_medida_logistica ?? 'N/A',
                        'subido' => $stockActual - $deudaArrastrada + $vendidoHoy, // Stock inicial cargado (Amortizado si hubo deuda)
                        'stock' => $saldoSif,                // Saldo neto actual SIF
                        'vendido' => $vendidoHoy                // Cantidad vendida hoy
                    ];
                })->toJson() !!},

                get filteredItems() {
                    return this.items.filter(item => {
                        const s = this.search.toLowerCase().trim();
                        const matchesSearch = s === '' ||
                            (item.codigo || '').toLowerCase().includes(s) ||
                            (item.nombre || '').toLowerCase().includes(s) ||
                            (item.linea || '').toLowerCase().includes(s);

                        let matchesStock = true;
                        if (this.stockFilter === 'with') {
                            matchesStock = item.stock > 0;
                        } else if (this.stockFilter === 'without') {
                            matchesStock = item.stock <= 0;
                        }

                        let matchesMovement = true;
                        if (this.movementFilter === 'with') {
                            matchesMovement = item.vendido > 0;
                        } else if (this.movementFilter === 'without') {
                            matchesMovement = item.vendido === 0;
                        }

                        return matchesSearch && matchesStock && matchesMovement;
                    });
                },

                formatNumber(val) {
                    const num = Number(val);
                    return isNaN(num) ? '0.000' : num.toFixed(3);
                },

                exportarFiltrados() {
                    let contenido = '\uFEFF'; // BOM UTF-8 para Excel de Windows
                    const cabeceras = ['CÓDIGO', 'PRODUCTO', 'LÍNEA', 'U/M', 'SUBIDO HOY', 'VENDIDO HOY', 'SALDO SIF'];
                    contenido += cabeceras.join(';') + '\n';
                    
                    this.filteredItems.forEach(item => {
                        const fila = [
                            item.codigo || '',
                            item.nombre || '',
                            item.linea || '',
                            item.unidad || '',
                            (Number(item.subido) || 0).toFixed(3),
                            (Number(item.vendido) || 0).toFixed(3),
                            (Number(item.stock) || 0).toFixed(3)
                        ];
                        
                        // Escapar cada campo para CSV seguro
                        const filaEscapada = fila.map(val => {
                            let str = String(val);
                            // Si contiene punto y coma, comillas o saltos de línea, lo envolvemos en comillas y duplicamos las comillas internas
                            if (str.includes(';') || str.includes('"') || str.includes('\n') || str.includes('\r')) {
                                str = '"' + str.replace(/"/g, '""') + '"';
                            }
                            return str;
                        });
                        
                        contenido += filaEscapada.join(';') + '\n';
                    });
                    
                    const blob = new Blob([contenido], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.setAttribute('href', url);
                    link.setAttribute('download', `cierre_diario_filtrado_${this.search ? 'filtrado' : 'completo'}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                },

                showDiagModal: false,
                loadingDiag: false,
                diagProducto: null,
                diagVentas: [],
                diagComprometidos: [],

                verMovimientosDiagnostico(codigo) {
                    this.showDiagModal = true;
                    this.loadingDiag = true;
                    this.diagProducto = null;
                    this.diagVentas = [];
                    this.diagComprometidos = [];

                    fetch(`/reportes/diagnostico-stock/${codigo}`)
                        .then(res => {
                            if (!res.ok) throw new Error('Error al obtener el diagnóstico');
                            return res.json();
                        })
                        .then(data => {
                            this.diagProducto = data.producto;
                            this.diagVentas = data.ventas_hoy;
                            this.diagComprometidos = data.comprometidos_futuros;
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Error al cargar la información de diagnóstico.');
                            this.showDiagModal = false;
                        })
                        .finally(() => {
                            this.loadingDiag = false;
                        });
                }
            };
        }
    </script>

    <div class="py-6 md:py-12 bg-gray-50 min-h-screen px-2" x-data="cierreDiarioData()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Botón Volver, PDF y Títulos -->
            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-emerald-600 pl-3">
                        Auditoría de Cierre de Inventario
                    </h3>
                    <p class="text-xs md:text-sm text-gray-500 mt-1 pl-3">
                        Fecha de control: <span class="font-semibold text-gray-700">{{ date('d/m/Y') }}</span>. Comparativa entre stock inicial, ventas y saldo SIF.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Botón Exportar Excel (CSV) -->
                    <button 
                        @click="exportarFiltrados()" 
                        class="inline-flex items-center justify-center text-white text-xs md:text-sm font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider w-full sm:w-auto cursor-pointer border-none"
                        style="background-color: #111827; color: #ffffff; padding: 8px 16px; border-radius: 8px; border: none; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;"
                        onmouseover="this.style.backgroundColor='#000000'"
                        onmouseout="this.style.backgroundColor='#111827'"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar Excel
                    </button>

                    <!-- Botón Exportar PDF -->
                    <a 
                        href="{{ route('reportes.cierre_diario.descargar') }}" 
                        class="inline-flex items-center justify-center text-white text-xs md:text-sm font-bold py-2 px-4 rounded-lg shadow-md transition duration-150 uppercase tracking-wider w-full sm:w-auto text-center border-none"
                        style="background-color: #b91c1c; color: #ffffff; padding: 8px 16px; border-radius: 8px; border: none; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
                        onmouseover="this.style.backgroundColor='#991b1b'"
                        onmouseout="this.style.backgroundColor='#b91c1c'"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Exportar PDF
                    </a>

                    <a href="{{ route('pedidos.index') }}" class="inline-flex items-center justify-center text-xs md:text-sm font-semibold text-gray-600 hover:text-gray-900 transition w-full sm:w-auto text-center py-2 text-decoration-none" style="text-decoration: none;">
                        &larr; Volver a Pedidos
                    </a>
                </div>
            </div>

            <!-- Panel de Filtros en Tiempo Real -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <!-- Buscar -->
                <div class="col-span-1 md:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-gray-600 mb-1">Buscar Producto</label>
                    <div class="relative">
                        <input 
                            x-model="search"
                            type="text" 
                            id="search"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white" 
                            placeholder="Código, nombre o línea..."
                        >
                    </div>
                </div>

                <!-- Selector de Nivel de Stock -->
                <div>
                    <label for="stockFilter" class="block text-xs font-semibold text-gray-600 mb-1">Nivel de Stock</label>
                    <select 
                        x-model="stockFilter"
                        id="stockFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white text-gray-700"
                    >
                        <option value="all">Todos</option>
                        <option value="with">Con Saldo (&gt; 0.000)</option>
                        <option value="without">Saldo en 0 (= 0.000)</option>
                    </select>
                </div>

                <!-- Selector de Movimientos del Día -->
                <div>
                    <label for="movementFilter" class="block text-xs font-semibold text-gray-600 mb-1">Movimientos del Día</label>
                    <select 
                        x-model="movementFilter"
                        id="movementFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white text-gray-700"
                    >
                        <option value="all">Todos</option>
                        <option value="with">Con ventas hoy (&gt; 0)</option>
                        <option value="without">Sin ventas hoy (= 0)</option>
                    </select>
                </div>
            </div>

            <!-- Tabla Principal -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                <div class="p-4 md:p-6 bg-white">
                    <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-600 text-white font-bold whitespace-nowrap">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">Código</th>
                                    <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold min-w-[350px] w-2/5">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs uppercase tracking-wider font-semibold">Línea</th>
                                    <th class="px-6 py-3 text-center text-xs uppercase tracking-wider font-semibold">U/M</th>
                                    <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">Subido Hoy</th>
                                    <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">Vendido Hoy</th>
                                    <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in filteredItems" :key="item.codigo">
                                    <tr 
                                        class="hover:bg-gray-50 transition"
                                        :class="item.stock <= 0 ? 'bg-red-50/70 text-red-700 font-bold' : ''"
                                    >
                                        <!-- Código -->
                                        <td 
                                            class="px-6 py-4 text-sm whitespace-nowrap"
                                            :class="item.stock <= 0 ? 'text-red-700 font-bold' : 'text-gray-900 font-semibold'"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <button 
                                                    @click="verMovimientosDiagnostico(item.codigo)"
                                                    title="Auditar Movimientos"
                                                    class="text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg p-1 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                                    style="background: transparent; border: none; cursor: pointer;"
                                                >
                                                    <i class="fas fa-history text-sm"></i>
                                                </button>
                                                <span x-text="item.codigo"></span>
                                            </div>
                                        </td>
                                        
                                        <!-- Producto -->
                                        <td 
                                            class="px-6 py-4 text-sm min-w-[350px] w-2/5"
                                            :class="item.stock <= 0 ? 'text-red-700 font-bold' : 'text-gray-600'"
                                            x-text="item.nombre"
                                        ></td>
                                        
                                        <!-- Línea -->
                                        <td 
                                            class="px-6 py-4 text-sm whitespace-nowrap"
                                            :class="item.stock <= 0 ? 'text-red-700 font-bold' : 'text-gray-600'"
                                            x-text="item.linea"
                                        ></td>
                                        
                                        <!-- U/M -->
                                        <td 
                                            class="px-6 py-4 text-sm text-center whitespace-nowrap"
                                            :class="item.stock <= 0 ? 'text-red-700 font-bold' : 'text-gray-600'"
                                            x-text="item.unidad"
                                        ></td>
                                        
                                        <!-- Subido Hoy -->
                                        <td 
                                            class="px-6 py-4 text-sm text-right font-mono whitespace-nowrap"
                                            :class="item.stock <= 0 ? 'text-red-700 font-bold' : 'text-gray-900'"
                                            x-text="formatNumber(item.subido)"
                                        ></td>

                                        <!-- Vendido Hoy -->
                                        <td 
                                            class="px-6 py-4 text-sm text-right font-mono whitespace-nowrap"
                                            :class="item.stock <= 0 ? 'text-red-700 font-bold' : (item.vendido > 0 ? 'text-blue-600 font-bold' : 'text-gray-600')"
                                            x-text="formatNumber(item.vendido)"
                                        ></td>
                                        
                                        <!-- Saldo SIF -->
                                        <td 
                                            class="px-6 py-4 text-sm text-right font-mono whitespace-nowrap"
                                            :class="item.stock <= 0 ? 'text-red-700 font-bold' : 'text-gray-900 font-semibold'"
                                        >
                                            <span x-show="item.stock <= 0" class="inline-block px-1.5 py-0.5 rounded bg-red-100 text-red-800 text-[10px] uppercase font-bold mr-1.5 align-middle">Ruptura</span>
                                            <span x-text="formatNumber(item.stock)"></span>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Fila de Sin Resultados -->
                                <tr x-show="filteredItems.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Ningún producto coincide con los filtros aplicados actualmente.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Diagnóstico de Stock -->
    <div 
        x-show="showDiagModal" 
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;"
    >
        <!-- Backdrop -->
        <div 
            x-show="showDiagModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showDiagModal = false"
            class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"
        ></div>

        <!-- Modal Content Container -->
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div 
                x-show="showDiagModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-8 w-full max-w-4xl border border-gray-100 flex flex-col max-h-[85vh]"
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-emerald-500/20 rounded-lg">
                            <i class="fas fa-history text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold leading-tight">Auditoría Rápida de Stock</h3>
                            <p class="text-xs text-emerald-100 mt-0.5" x-text="diagProducto ? diagProducto.codigo + ' - ' + diagProducto.nombre : 'Cargando...'"></p>
                        </div>
                    </div>
                    <button 
                        @click="showDiagModal = false" 
                        class="text-white/80 hover:text-white hover:bg-emerald-500/30 p-1.5 rounded-lg transition focus:outline-none"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body (Scrollable) -->
                <div class="p-6 overflow-y-auto flex-1 bg-gray-50/50 space-y-6">
                    <!-- Spinner de Carga -->
                    <div x-show="loadingDiag" class="py-16 flex flex-col items-center justify-center space-y-4">
                        <div class="relative w-12 h-12">
                            <div class="absolute inset-0 rounded-full border-4 border-emerald-100"></div>
                            <div class="absolute inset-0 rounded-full border-4 border-emerald-600 border-t-transparent animate-spin"></div>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Obteniendo movimientos del producto en tiempo real...</p>
                    </div>

                    <!-- Datos cargados -->
                    <div x-show="!loadingDiag && diagProducto" class="space-y-6" style="display: none;">
                        
                        <!-- Tarjetas de Métricas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Carga Mañana (Planilla) -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Carga Mañana (Planilla)</span>
                                <div class="mt-2 flex items-baseline space-x-1">
                                    <span class="text-2xl font-black text-blue-900" x-text="'+' + formatNumber(diagProducto.stock_planilla)"></span>
                                    <span class="text-xs text-blue-700 font-semibold" x-text="diagProducto.unidad"></span>
                                </div>
                                <span class="text-[10px] text-blue-500 mt-2 font-medium">Stock inicial cargado en el día</span>
                            </div>

                            <!-- Ventas del Día -->
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Vendido Hoy</span>
                                <div class="mt-2 items-baseline space-x-1">
                                    <span class="text-2xl font-black text-amber-900" x-text="'-' + formatNumber(diagVentas.reduce((acc, v) => acc + (['Rechazado', 'Anulado'].includes(v.estado) ? 0 : v.cantidad), 0))"></span>
                                    <span class="text-xs text-amber-700 font-semibold" x-text="diagProducto.unidad"></span>
                                </div>
                                <span class="text-[10px] text-amber-500 mt-2 font-medium">Pedidos procesados hoy</span>
                            </div>

                            <!-- Comprometido Futuro -->
                            <div class="bg-gradient-to-br from-purple-50 to-fuchsia-50 border border-purple-100 rounded-xl p-4 shadow-sm flex flex-col justify-between">
                                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Comprometido Futuro</span>
                                <div class="mt-2 items-baseline space-x-1">
                                    <span class="text-2xl font-black text-purple-900" x-text="'-' + formatNumber(diagProducto.comprometido_futuro)"></span>
                                    <span class="text-xs text-purple-700 font-semibold" x-text="diagProducto.unidad"></span>
                                </div>
                                <span class="text-[10px] text-purple-500 mt-2 font-medium">Despachos programados a 2d+</span>
                            </div>

                            <!-- Saldo Disponible (SIF) -->
                            <div 
                                class="border rounded-xl p-4 shadow-sm flex flex-col justify-between transition-colors"
                                :class="diagProducto.saldo_sif < 0 ? 'bg-gradient-to-br from-red-50 to-pink-50 border-red-200' : 'bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-200'"
                            >
                                <span 
                                    class="text-xs font-semibold uppercase tracking-wider"
                                    :class="diagProducto.saldo_sif < 0 ? 'text-red-600' : 'text-emerald-600'"
                                >
                                    Saldo SIF (Disponible)
                                </span>
                                <div class="mt-2 items-baseline space-x-1">
                                    <span 
                                        class="text-2xl font-black"
                                        :class="diagProducto.saldo_sif < 0 ? 'text-red-900' : 'text-emerald-900'"
                                        x-text="formatNumber(diagProducto.saldo_sif)"
                                    ></span>
                                    <span 
                                        class="text-xs font-semibold"
                                        :class="diagProducto.saldo_sif < 0 ? 'text-red-700' : 'text-emerald-700'"
                                        x-text="diagProducto.unidad"
                                    ></span>
                                </div>
                                <span 
                                    class="text-[10px] mt-2 font-medium"
                                    :class="diagProducto.saldo_sif < 0 ? 'text-red-500' : 'text-emerald-500'"
                                    x-text="diagProducto.saldo_sif < 0 ? '¡Alerta de Stock Negativo!' : 'Stock disponible neto'"
                                ></span>
                            </div>
                        </div>

                        <!-- Info Adicional de Auditoría -->
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 text-xs text-blue-800 space-y-1">
                            <h4 class="font-bold flex items-center gap-1.5"><i class="fas fa-info-circle"></i> Nota de Auditoría de Cómputo:</h4>
                            <p>
                                <strong>Carga Mañana (Stock Inicial Subido Hoy):</strong> <span class="font-mono font-bold" x-text="formatNumber(Number(diagProducto.stock_actual) - Number(diagProducto.deuda_arrastrada) + Number(diagProducto.vendido_hoy_futuro)) + ' ' + diagProducto.unidad"></span>.
                            </p>
                            <p>
                                <strong>Fórmula de Balance:</strong> Saldo SIF (<span class="font-mono" x-text="formatNumber(diagProducto.saldo_sif)"></span>) = Carga Mañana (<span class="font-mono" x-text="formatNumber(Number(diagProducto.stock_actual) - Number(diagProducto.deuda_arrastrada) + Number(diagProducto.vendido_hoy_futuro))"></span>) - Comprometido Futuro (<span class="font-mono" x-text="formatNumber(diagProducto.comprometido_futuro)"></span>).
                            </p>
                        </div>

                        <!-- Sección de Pedidos del Día -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                            <div class="px-5 py-3 border-b border-gray-150 bg-gray-50 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-shopping-cart text-gray-400"></i> Pedidos del Día de Hoy (Ventas)
                                </h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 text-gray-600" x-text="diagVentas.length + ' item(s)'"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50/80 font-bold text-gray-500 uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="px-5 py-2.5 text-left">N° Pedido</th>
                                            <th class="px-5 py-2.5 text-left">Vendedora</th>
                                            <th class="px-5 py-2.5 text-right">Cantidad Vendida</th>
                                            <th class="px-5 py-2.5 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="item in diagVentas" :key="item.pedido">
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="px-5 py-3 font-semibold text-gray-900" x-text="item.pedido"></td>
                                                <td class="px-5 py-3 text-gray-600" x-text="item.vendedora"></td>
                                                <td class="px-5 py-3 text-right font-mono text-gray-800" x-text="formatNumber(item.cantidad) + ' ' + diagProducto.unidad"></td>
                                                <td class="px-5 py-3 text-center">
                                                    <span 
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                        :class="{
                                                            'bg-green-100 text-green-800': item.estado === 'Aprobado',
                                                            'bg-yellow-100 text-yellow-800': item.estado === 'Pendiente',
                                                            'bg-red-100 text-red-800': ['Rechazado', 'Anulado'].includes(item.estado),
                                                            'bg-blue-100 text-blue-800': !['Aprobado', 'Pendiente', 'Rechazado', 'Anulado'].includes(item.estado)
                                                        }"
                                                        x-text="item.estado"
                                                    ></span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="diagVentas.length === 0">
                                            <td colspan="4" class="px-5 py-8 text-center text-gray-400">
                                                No se registran ventas para este producto el día de hoy.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Sección de Comprometidos a Futuro -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                            <div class="px-5 py-3 border-b border-gray-150 bg-gray-50 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-gray-400"></i> Stock Comprometido a Futuro (Despacho a 2 días o más)
                                </h4>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700" x-text="diagComprometidos.length + ' item(s)'"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50/80 font-bold text-gray-500 uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="px-5 py-2.5 text-left">N° Pedido</th>
                                            <th class="px-5 py-2.5 text-left">Vendedora</th>
                                            <th class="px-5 py-2.5 text-left">Fecha Despacho</th>
                                            <th class="px-5 py-2.5 text-right">Cantidad Comprometida</th>
                                            <th class="px-5 py-2.5 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="item in diagComprometidos" :key="item.pedido">
                                            <tr class="hover:bg-gray-50/50 transition">
                                                <td class="px-5 py-3 font-semibold text-gray-900" x-text="item.pedido"></td>
                                                <td class="px-5 py-3 text-gray-600" x-text="item.vendedora"></td>
                                                <td class="px-5 py-3 text-gray-600" x-text="item.fecha_entrega ? item.fecha_entrega.split('-').reverse().join('/') : 'N/A'"></td>
                                                <td class="px-5 py-3 text-right font-mono text-gray-800" x-text="formatNumber(item.cantidad) + ' ' + diagProducto.unidad"></td>
                                                <td class="px-5 py-3 text-center">
                                                    <span 
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                                        x-text="item.estado"
                                                    ></span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="diagComprometidos.length === 0">
                                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">
                                                No hay stock comprometido para despachos futuros programados.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-150 flex justify-end space-x-3">
                    <button 
                        @click="showDiagModal = false" 
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none transition shadow-sm"
                        style="cursor: pointer;"
                    >
                        Cerrar Auditoría
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
