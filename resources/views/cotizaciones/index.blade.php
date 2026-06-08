<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestión de Cotizaciones') }}
        </h2>
    </x-slot>

    <div x-data="{ modalVentaPerdidaOpen: false, selectedCotizacionId: null }" class="py-6 md:py-12 bg-gray-50 min-h-screen px-2">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <h3 class="text-base md:text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Cotizaciones Recientes</h3>
                        <a href="{{ route('cotizaciones.create') }}" class="w-full sm:w-auto bg-fenix-green hover:bg-[#12311f] text-white font-bold py-3 sm:py-2 px-4 rounded shadow text-center transition-all">
                            + Nueva Cotización
                        </a>
                    </div>
                    
                    @if (session('success'))
                        <div class="bg-green-100 text-green-700 px-4 py-3 rounded relative mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('warning'))
                        <div class="bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('warning') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 text-red-700 px-4 py-3 rounded relative mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 mt-4">
                            <thead class="bg-fenix-green">
                                <tr>
                                    <th class="px-3 md:px-6 py-3 text-left text-[10px] md:text-xs font-medium text-white uppercase tracking-wider">Número</th>
                                    <th class="px-3 md:px-6 py-3 text-left text-[10px] md:text-xs font-medium text-white uppercase tracking-wider">Cliente</th>
                                    <th class="px-3 md:px-6 py-3 text-left text-[10px] md:text-xs font-medium text-white uppercase tracking-wider">Plantilla</th>
                                    <th class="px-3 md:px-6 py-3 text-left text-[10px] md:text-xs font-medium text-white uppercase tracking-wider">Total</th>
                                    <th class="px-3 md:px-6 py-3 text-left text-[10px] md:text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                                    <th class="px-3 md:px-6 py-3 text-left text-[10px] md:text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($cotizaciones as $c)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 md:px-6 py-4 text-xs md:text-sm font-bold text-gray-900">{{ $c->numero }}</td>
                                        <td class="px-3 md:px-6 py-4 text-xs md:text-sm text-gray-600">{{ $c->cliente->nombre }}</td>
                                        <td class="px-3 md:px-6 py-4 text-xs md:text-sm text-gray-600">{{ $c->plantilla->nombre }}</td>
                                        <td class="px-3 md:px-6 py-4 text-xs md:text-sm font-bold text-gray-900">{{ $c->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($c->total, 2) }}</td>
                                        <td class="px-3 md:px-6 py-4 text-xs md:text-sm">
                                            <span class="px-2 inline-flex text-[10px] md:text-xs leading-5 font-semibold rounded-full 
                                                {{ $c->estado == 'Borrador' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $c->estado == 'Convertida a Pedido' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $c->estado == 'Anulado' ? 'bg-red-100 text-red-800' : '' }}
                                            ">
                                                {{ $c->estado }}
                                            </span>
                                        </td>
                                        <td class="px-3 md:px-6 py-4 text-xs md:text-sm flex flex-wrap gap-1 md:gap-2">
                                            <a href="{{ route('cotizaciones.show', $c) }}" class="bg-blue-500 text-white px-2 py-1 rounded text-[10px] md:text-xs hover:bg-blue-600">Ver</a>
                                            
                                            @role('Vendedor')

                                                <a href="{{ route('cotizaciones.duplicar', $c->id) }}" class="bg-cyan-500 hover:bg-cyan-600 text-white px-2 py-1 rounded text-[10px] md:text-xs shadow-sm transition">
                                                <a href="{{ route('cotizaciones.duplicar', $c->id) }}" class="bg-black text-white px-2 py-1 rounded text-[10px] md:text-xs hover:bg-gray-900 transition">
                                                    Duplicar
                                                </a>
                                            @endrole
                                            
                                            @if($c->estado === 'Borrador')
                                                <a href="{{ route('cotizaciones.edit', $c) }}" class="bg-yellow-500 text-black px-2 py-1 rounded text-[10px] md:text-xs hover:bg-yellow-600">Editar</a>
                                            @endif
                                            <!-- Dropdown Descargar -->
                                            <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
                                                <button type="button" @click="open = !open" class="bg-gray-800 text-white px-2 py-1 rounded text-[10px] md:text-xs hover:bg-black inline-flex items-center gap-1 transition focus:outline-none">
                                                    <span>Descargar</span>
                                                    <svg class="h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <div x-show="open" 
                                                     x-transition:enter="transition ease-out duration-100" 
                                                     x-transition:enter-start="transform opacity-0 scale-95" 
                                                     x-transition:enter-end="transform opacity-100 scale-100" 
                                                     x-transition:leave="transition ease-in duration-75" 
                                                     x-transition:leave-start="transform opacity-100 scale-100" 
                                                     x-transition:leave-end="transform opacity-0 scale-95" 
                                                     class="origin-top-right absolute right-0 mt-1 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 divide-y divide-gray-100 focus:outline-none" 
                                                     style="display: none;">
                                                    <div class="py-1">
                                                        <a href="{{ route('cotizaciones.pdf', $c) }}" target="_blank" @click="open = false" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 group flex items-center px-3 py-1.5 text-[10px] md:text-xs font-medium">
                                                            <svg class="mr-2 h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                            Descargar PDF
                                                        </a>
                                                        <a href="{{ route('cotizaciones.jpg', $c) }}" target="_blank" @click="open = false" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 group flex items-center px-3 py-1.5 text-[10px] md:text-xs font-medium">
                                                            <svg class="mr-2 h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                            Descargar JPG
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @if($c->estado == 'Borrador')
                                                <button type="button" @click="selectedCotizacionId = {{ $c->id }}; modalVentaPerdidaOpen = true" class="bg-red-500 text-white px-2 py-1 rounded text-[10px] md:text-xs hover:bg-red-600 shadow transition">
                                                    Anular
                                                </button>
                                            @endif

                                            @if($c->estado === 'Borrador')
                                                <form action="{{ route('pedidos.store') }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="cotizacion_id" value="{{ $c->id }}">
                                                    <button type="submit" class="bg-fenix-gold text-black font-bold px-2 py-1 rounded text-[10px] md:text-xs hover:bg-yellow-500 shadow-sm border border-yellow-600">Confirmar</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-3 md:px-6 py-4 text-xs md:text-sm text-center">No hay cotizaciones.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @include('crm.modal_venta_perdida')
    </div>
</x-app-layout>