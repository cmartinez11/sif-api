<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestión de Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Listado de Clientes</h3>
                        
                        {{-- Solo Administradores y Supervisores pueden ver este botón --}}
                        @hasanyrole('Administrador|Supervisor|Vendedor')
                            <a href="{{ route('clientes.create') }}" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150">
                                + Añadir Cliente
                            </a>
                        @endhasanyrole
                    </div>

                    {{-- Lógica de búsqueda igual a la de Productos --}}
                    <div class="mb-5 flex justify-end">
                        <form action="{{ route('clientes.index') }}" method="GET" class="relative w-full md:w-1/3">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input 
                                name="search"
                                value="{{ request('search') }}"
                                type="text" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-fenix-green focus:border-fenix-green sm:text-sm transition duration-150 ease-in-out" 
                                placeholder="Buscar por RUC o nombre de cliente..."
                                x-data
                                @input.debounce.500ms="$el.form.submit()"
                                {{ request('search') ? 'autofocus' : '' }}
                                onfocus="var temp_value=this.value; this.value=''; this.value=temp_value"
                            >
                        </form>
                    </div>

                        @if (session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-fenix-green">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">RUC</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre / Razón Social</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Dirección</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ubicación</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($clientes as $cliente)
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $cliente->ruc }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $cliente->nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $cliente->direccion }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $cliente->departamento ?? '-' }} - {{ $cliente->provincia ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-3">
                                                {{-- Mismas restricciones de acciones que en Productos --}}
                                                @hasanyrole('Administrador|Supervisor')
                                                    <a href="{{ route('clientes.edit', $cliente) }}" class="text-fenix-green hover:text-indigo-900">Editar</a>
                                                    <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                    </form>
                                                @elseif(auth()->user()->hasRole('Vendedor'))
                                                    <a href="{{ route('clientes.edit', $cliente) }}" class="text-blue-500 hover:text-blue-700 font-bold flex items-center gap-1">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </a>
                                                @else
                                                    <span class="text-gray-400 italic">Sin acciones</span>
                                                @endhasanyrole
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-gray-500 text-sm italic bg-gray-50">
                                                No se encontraron clientes que coincidan con su búsqueda.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 p-4">
                            {{ $clientes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>