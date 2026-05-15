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
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                                <span>{{ session('success') }}</span>
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