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
                                    
                                    <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-5 rounded-lg shadow-md transition ease-in-out duration-150 uppercase tracking-wider">
                                        Sincronizar Stock
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endhasanyrole

                    <div x-data="{ 
                        search: '', 
                        products: {{ $productos->map(fn($p) => ['codigo' => $p->codigo, 'nombre' => $p->nombre, 'linea' => $p->linea])->toJson() }},
                        get hasResults() {
                            if (this.search === '') return true;
                            const s = this.search.toLowerCase();
                            return this.products.some(p => 
                                p.codigo.toLowerCase().includes(s) || 
                                p.nombre.toLowerCase().includes(s) ||
                                (p.linea && p.linea.toLowerCase().includes(s))
                            );
                        }
                    }">
                        <div class="mb-5 flex justify-end">
                            <div class="relative w-full sm:w-1/2 lg:w-1/3">
                                <input 
                                    x-model="search"
                                    type="text" 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-fenix-green focus:border-fenix-green text-sm" 
                                    placeholder="Buscar por nombre, código o línea..."
                                >
                            </div>
                        </div>

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

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-fenix-green text-white font-bold whitespace-nowrap">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs uppercase">Código</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase min-w-[200px]">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase">Línea</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase">P. Base</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($productos as $producto)
                                        <tr 
                                            x-show="!search || '{{ $producto->codigo }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $producto->nombre }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $producto->linea }}'.toLowerCase().includes(search.toLowerCase())"
                                            class="hover:bg-gray-50 transition"
                                        >
                                            <td class="px-4 py-4 text-sm font-bold text-gray-900">{{ $producto->codigo }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-600">{{ $producto->nombre }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-600">{{ $producto->linea ?? 'N/A' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-600">{{ number_format($producto->precio_base, 2) }}</td>
                                            <td class="px-4 py-4 text-sm">
                                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $producto->estado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $producto->estado ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-sm font-medium">
                                                <div class="flex gap-2">
                                                    @hasanyrole('Administrador|Supervisor')
                                                        <a href="{{ route('productos.edit', $producto) }}" class="text-blue-600 hover:text-blue-900">Editar</a>
                                                        <form action="{{ route('productos.destroy', $producto) }}" method="POST">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('¿Eliminar?')">Borrar</button>
                                                        </form>
                                                    @endhasanyrole
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-6 py-4 text-center">No hay productos.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>