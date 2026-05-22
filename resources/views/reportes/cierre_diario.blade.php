<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Reporte de Cierre Diario') }}
        </h2>
    </x-slot>

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
                    return [
                        'codigo' => $p->codigo,
                        'nombre' => $p->nombre, // Directo sin escapes duplicados para que Alpine lo lea correctamente
                        'linea' => $p->linea ?? 'N/A',
                        'unidad' => $p->unidad_medida_logistica ?? 'N/A',
                        'subido' => $stockActual - $deudaArrastrada + $vendidoHoy, // Stock inicial cargado (Amortizado si hubo deuda)
                        'stock' => $stockActual,                // Saldo neto actual SIF
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
                                    <th class="px-6 py-3 text-right text-xs uppercase tracking-wider font-semibold">Saldo SIF</th>
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
                                            x-text="item.codigo"
                                        ></td>
                                        
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
    </div>
</x-app-layout>
