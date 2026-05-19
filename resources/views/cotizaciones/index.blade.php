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
                                                    Duplicar
                                                </a>
                                            @endrole
                                            
                                            @if($c->estado === 'Borrador')
                                                <a href="{{ route('cotizaciones.edit', $c) }}" class="bg-yellow-500 text-black px-2 py-1 rounded text-[10px] md:text-xs hover:bg-yellow-600">Editar</a>
                                            @endif
                                            <a href="{{ route('cotizaciones.pdf', $c) }}" target="_blank" class="bg-gray-800 text-white px-2 py-1 rounded text-[10px] md:text-xs hover:bg-black">PDF</a>
                                            
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