<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Catálogo de Productos') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-gray-50 min-h-screen px-2">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4 md:p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-fenix-gold pl-3">Inventario Maestro</h3>
                        @hasanyrole('Administrador|Supervisor')
                            <a href="{{ route('productos.create') }}" class="w-full sm:w-auto text-center bg-fenix-green hover:bg-[#12311f] text-white font-bold py-3 sm:py-2 px-6 rounded shadow transition ease-in-out duration-150">
                                + Añadir Producto
                            </a>
                        @endhasanyrole
                    </div>

                    @hasanyrole('Administrador|Supervisor')
                    <!-- Card de Carga Masiva de Stock -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border border-gray-200 mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Carga Masiva de Stock Diario
                                </h3>
                                <p class="text-xs text-gray-500 mt-1">Descarga la plantilla estructurada, completa los saldos del día y sube el archivo para sincronizar el almacén.</p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                                <a href="{{ route('productos.descargar_plantilla') }}" class="w-full sm:w-auto text-center border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-bold py-2 px-4 rounded-lg shadow-sm transition duration-150 uppercase tracking-wider flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Descargar Plantilla
                                </a>

                                <form action="{{ route('productos.cargar_stock_diario') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                                    @csrf
                                    <div class="relative w-full sm:w-48">
                                        <input type="file" name="archivo_stock" id="archivo_stock" required accept=".xls,.csv,.txt"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                            onchange="document.getElementById('fileNameSpan').innerText = this.files[0] ? this.files[0].name : 'Adjuntar archivo'">
                                        <div class="border border-dashed border-gray-300 rounded-lg p-2 text-center text-xs font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 transition duration-150 truncate" id="fileNameSpan">
                                            Adjuntar archivo
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="w-full sm:w-auto bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-2 px-5 rounded-lg shadow-md transition ease-in-out duration-150 uppercase tracking-wider">
                                        Sincronizar Stock
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- Wrapper de Alpine.js -->
                    <div x-data="{
                        search: '',
                        stockFilter: 'all',
                        lineaFilter: '',
                        currentPage: 1,
                        pageSize: 20,
                        products: {{
                            $productos->map(fn($p) => [
                                'id' => $p->id,
                                'codigo' => $p->codigo,
                                'nombre' => $p->nombre,
                                'linea' => $p->linea ?? 'N/A',
                                'unidad_medida' => $p->unidad_medida ?? 'N/A',
                                'stock' => (float)($p->stock ?? 0),
                                'estado' => (bool)$p->estado,
                                'editUrl' => route('productos.edit', $p),
                                'deleteUrl' => route('productos.destroy', $p)
                            ])->toJson()
                        }},
                        canEdit: @hasanyrole('Administrador|Supervisor') true @else false @endhasanyrole,

                        get filteredProducts() {
                            return this.products.filter(p => {
                                // Filtro de búsqueda (Código, Nombre o Línea)
                                const s = this.search.toLowerCase().trim();
                                const matchesSearch = s === '' ||
                                    p.codigo.toLowerCase().includes(s) ||
                                    p.nombre.toLowerCase().includes(s) ||
                                    p.linea.toLowerCase().includes(s);

                                // Filtro de Stock
                                let matchesStock = true;
                                if (this.stockFilter === 'with') {
                                    matchesStock = p.stock > 0;
                                } else if (this.stockFilter === 'without') {
                                    matchesStock = p.stock === 0;
                                }

                                // Filtro de Línea
                                const matchesLinea = this.lineaFilter === '' || p.linea === this.lineaFilter;

                                return matchesSearch && matchesStock && matchesLinea;
                            });
                        },

                        get paginatedProducts() {
                            const start = (this.currentPage - 1) * this.pageSize;
                            return this.filteredProducts.slice(start, start + this.pageSize);
                        },

                        get totalPages() {
                            return Math.ceil(this.filteredProducts.length / this.pageSize);
                        },

                        resetPage() {
                            this.currentPage = 1;
                        },

                        init() {
                            this.$watch('search', () => this.resetPage());
                            this.$watch('stockFilter', () => this.resetPage());
                            this.$watch('lineaFilter', () => this.resetPage());
                        },

                        deleteProduct(deleteUrl) {
                            if (confirm('¿Está seguro de que desea eliminar este producto?')) {
                                const form = document.getElementById('delete-product-form');
                                form.action = deleteUrl;
                                form.submit();
                            }
                        }
                    }">
                        <!-- Alertas -->
                        @if (session('success'))
                            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative mb-4 flex items-center gap-2 shadow-sm">
                                <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm font-medium">{{ session('success') }}</span>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative mb-4 flex items-start gap-2 shadow-sm">
                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm font-medium">
                                    <p class="font-bold">Error:</p>
                                    <p>{{ session('error') }}</p>
                                </div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative mb-4 flex items-start gap-2 shadow-sm">
                                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm font-medium">
                                    <p class="font-bold">Errores de Validación:</p>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <!-- Barra de Búsqueda Avanzada -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm">
                            <!-- Input de Búsqueda -->
                            <div>
                                <label for="search" class="block text-xs font-semibold text-gray-600 mb-1">Buscar producto</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </span>
                                    <input 
                                        x-model="search"
                                        type="text" 
                                        id="search"
                                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white" 
                                        placeholder="Código, nombre o línea..."
                                    >
                                </div>
                            </div>

                            <!-- Selector de Stock -->
                            <div>
                                <label for="stockFilter" class="block text-xs font-semibold text-gray-600 mb-1">Filtrar por Stock</label>
                                <select 
                                    x-model="stockFilter"
                                    id="stockFilter"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white text-gray-700"
                                >
                                    <option value="all">Todos</option>
                                    <option value="with">Con Stock Disp. (&gt; 0)</option>
                                    <option value="without">Sin Stock Disp. (= 0)</option>
                                </select>
                            </div>

                            <!-- Selector de Línea -->
                            <div>
                                <label for="lineaFilter" class="block text-xs font-semibold text-gray-600 mb-1">Filtrar por Línea</label>
                                <select 
                                    x-model="lineaFilter"
                                    id="lineaFilter"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white text-gray-700"
                                >
                                    <option value="">Todas las líneas</option>
                                    <option value="BOBINA AD">BOBINA AD</option>
                                    <option value="BOBINA BD">BOBINA BD</option>
                                    <option value="BOBINA PP">BOBINA PP</option>
                                    <option value="BOLSAS AD">BOLSAS AD</option>
                                    <option value="BOLSAS BD">BOLSAS BD</option>
                                    <option value="BOLSAS PP">BOLSAS PP</option>
                                    <option value="PET">PET</option>
                                    <option value="TERMOFORMADO PP">TERMOFORMADO PP</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabla Principal -->
                        <div class="overflow-x-auto shadow border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-green-600 text-white font-bold whitespace-nowrap">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Código</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider min-w-[200px]">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Línea</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Unidad de medida</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Stock Disp.</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Listado de Productos -->
                                    <template x-for="producto in paginatedProducts" :key="producto.id">
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-4 text-sm font-bold text-gray-900" x-text="producto.codigo"></td>
                                            <td class="px-4 py-4 text-sm text-gray-600" x-text="producto.nombre"></td>
                                            <td class="px-4 py-4 text-sm text-gray-600" x-text="producto.linea"></td>
                                            <td class="px-4 py-4 text-sm text-gray-600" x-text="producto.unidad_medida"></td>
                                            <td class="px-4 py-4 text-sm text-gray-600 font-mono" x-text="producto.stock.toFixed(3)"></td>
                                            <td class="px-4 py-4 text-sm">
                                                <span 
                                                    class="px-2 py-1 rounded-full text-xs font-bold" 
                                                    :class="producto.estado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                    x-text="producto.estado ? 'Activo' : 'Inactivo'"
                                                ></span>
                                            </td>
                                            <td class="px-4 py-4 text-sm font-medium">
                                                <div class="flex gap-2">
                                                    <!-- Botones permitidos para Administrador/Supervisor -->
                                                    <template x-if="canEdit">
                                                        <div class="flex gap-2">
                                                            <a :href="producto.editUrl" class="text-blue-600 hover:text-blue-900 transition">Editar</a>
                                                            <button type="button" @click="deleteProduct(producto.deleteUrl)" class="text-red-600 hover:text-red-900 transition">Borrar</button>
                                                        </div>
                                                    </template>
                                                    <!-- Texto de solo lectura si no tiene privilegios -->
                                                    <template x-if="!canEdit">
                                                        <span class="text-gray-400 text-xs italic">Solo lectura</span>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>

                                    <!-- Fila de Sin Resultados -->
                                    <template x-if="filteredProducts.length === 0">
                                        <tr>
                                            <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>Ningún producto coincide con los filtros aplicados actualmente.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación Local -->
                        <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 text-sm text-gray-600">
                            <!-- Contador de registros -->
                            <div>
                                <span x-text="'Mostrando de ' + (filteredProducts.length === 0 ? 0 : (currentPage - 1) * pageSize + 1) + ' a ' + Math.min(currentPage * pageSize, filteredProducts.length) + ' de ' + filteredProducts.length + ' productos'"></span>
                            </div>

                            <!-- Botones de navegación -->
                            <div class="flex items-center gap-2">
                                <!-- Primera Página -->
                                <button 
                                    type="button"
                                    @click="currentPage = 1"
                                    :disabled="currentPage === 1"
                                    class="px-2 py-1.5 rounded border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold text-gray-700 animate"
                                    title="Primera página"
                                >
                                    ≪
                                </button>
                                <!-- Anterior -->
                                <button 
                                    type="button"
                                    @click="currentPage = Math.max(1, currentPage - 1)"
                                    :disabled="currentPage === 1"
                                    class="px-2 py-1.5 rounded border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold text-gray-700"
                                    title="Página anterior"
                                >
                                    ‹
                                </button>

                                <!-- Indicador de página -->
                                <span class="px-3 font-semibold text-emerald-600">
                                    Pág. <span x-text="totalPages === 0 ? 0 : currentPage"></span> de <span x-text="totalPages"></span>
                                </span>

                                <!-- Siguiente -->
                                <button 
                                    type="button"
                                    @click="currentPage = Math.min(totalPages, currentPage + 1)"
                                    :disabled="currentPage === totalPages || totalPages === 0"
                                    class="px-2 py-1.5 rounded border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold text-gray-700"
                                    title="Página siguiente"
                                >
                                    ›
                                </button>
                                <!-- Última Página -->
                                <button 
                                    type="button"
                                    @click="currentPage = totalPages"
                                    :disabled="currentPage === totalPages || totalPages === 0"
                                    class="px-2 py-1.5 rounded border border-gray-300 bg-white hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition font-semibold text-gray-700"
                                    title="Última página"
                                >
                                    ≫
                                </button>
                            </div>
                        </div>

                        <!-- Formulario de Eliminación Oculto -->
                        <form id="delete-product-form" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>