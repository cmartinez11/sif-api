<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Seguimiento de Pedidos') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-gray-50 min-h-screen px-2">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-4 md:p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 bg-white p-4 rounded-lg shadow-sm border-t-4 border-fenix-gold gap-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
                            <h3 class="text-lg md:text-xl font-bold text-gray-800 border-l-4 border-fenix-gold pl-3 whitespace-nowrap">Listado de Pedidos</h3>
                            
                            <!-- Dropdown para Pedidos Directos -->
                            <div x-data="{ openDirecto: false }" class="relative inline-block text-left w-full sm:w-auto">
                                <button @click="openDirecto = !openDirecto" type="button" class="w-full sm:w-auto bg-[#0CC954] hover:bg-green-700 text-white font-bold px-4 py-2 rounded shadow text-xs transition flex items-center justify-center gap-1.5 uppercase">
                                    <i class="fas fa-plus"></i> Nuevo Pedido Directo <i class="fas fa-chevron-down text-[10px]"></i>
                                </button>
                                <div x-show="openDirecto" @click.away="openDirecto = false" class="origin-top-left absolute left-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" x-cloak>
                                    <div class="py-1">
                                        <a href="{{ route('pedidos.crear', 'universal') }}" class="text-gray-700 block px-4 py-2.5 text-xs hover:bg-gray-100 font-semibold"><i class="fas fa-file-invoice mr-2 text-emerald-500"></i> Universal</a>
                                        <a href="{{ route('pedidos.crear', 'tratadas') }}" class="text-gray-700 block px-4 py-2.5 text-xs hover:bg-gray-100 font-semibold"><i class="fas fa-file-invoice mr-2 text-emerald-500"></i> Tratadas</a>
                                        <a href="{{ route('pedidos.crear', 'bolsas-polipropileno') }}" class="text-gray-700 block px-4 py-2.5 text-xs hover:bg-gray-100 font-semibold"><i class="fas fa-file-invoice mr-2 text-emerald-500"></i> Bolsas de Polipropileno</a>
                                        <a href="{{ route('pedidos.crear', 'pets') }}" class="text-gray-700 block px-4 py-2.5 text-xs hover:bg-gray-100 font-semibold"><i class="fas fa-file-invoice mr-2 text-emerald-500"></i> Pets</a>
                                        <a href="{{ route('pedidos.crear', 'bolsas-polipropileno-kilos') }}" class="text-gray-700 block px-4 py-2.5 text-xs hover:bg-gray-100 font-semibold"><i class="fas fa-file-invoice mr-2 text-emerald-500"></i> Bolsas de Polipropileno por Kilos</a>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <form method="GET" action="{{ route('pedidos.index') }}" class="flex flex-col sm:flex-row flex-wrap items-end justify-end gap-3 text-xs w-full">
                            
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 bg-gray-50 p-2 rounded border w-full sm:w-auto">
                                <span class="text-gray-600 font-bold uppercase">Pedido:</span>
                                <div class="flex items-center space-x-1 w-full sm:w-auto">
                                    <span class="text-gray-400">Del</span>
                                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full sm:w-auto border-gray-300 rounded px-2 py-2 sm:py-1 focus:ring-fenix-green focus:border-fenix-green text-xs">
                                    <span class="text-gray-400">Al</span>
                                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full sm:w-auto border-gray-300 rounded px-2 py-2 sm:py-1 focus:ring-fenix-green focus:border-fenix-green text-xs">
                                </div>
                            </div>
    
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 bg-gray-50 p-2 rounded border w-full sm:w-auto">
                                <span class="text-gray-600 font-bold uppercase">Despacho:</span>
                                <input type="date" name="fecha_despacho" value="{{ request('fecha_despacho') }}" class="w-full sm:w-auto border-gray-300 rounded px-2 py-2 sm:py-1 focus:ring-fenix-green focus:border-fenix-green text-xs">
                            </div>
    
                            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                                <select name="estado" class="w-full sm:w-auto border-gray-300 rounded px-2 py-2 sm:py-1 focus:ring-fenix-green focus:border-fenix-green text-xs min-w-[140px]">
                                    <option value="">Todos los Estados</option>
                                    @foreach(\App\Models\Pedido::ESTADOS_ORDEN as $estadoOpcion)
                                        <option value="{{ $estadoOpcion }}" {{ request('estado') == $estadoOpcion ? 'selected' : '' }}>{{ $estadoOpcion }}</option>
                                    @endforeach
                                </select>
        
                                @if(!auth()->user()->hasRole('Vendedor'))
                                <select name="vendedor_id" class="w-full sm:w-auto border-gray-300 rounded px-2 py-2 sm:py-1 focus:ring-fenix-green focus:border-fenix-green text-xs min-w-[140px]">
                                    <option value="">Todos los Vendedores</option>
                                    @foreach($vendedores as $vend)
                                        <option value="{{ $vend->id }}" {{ request('vendedor_id') == $vend->id ? 'selected' : '' }}>
                                            {{ $vend->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @endif
                            </div>
    
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button type="submit" class="flex-1 sm:flex-none bg-fenix-green hover:bg-green-600 text-white px-6 py-2 rounded shadow-sm font-bold transition-colors">
                                    Filtrar
                                </button>
                                <a href="{{ route('pedidos.index') }}" class="flex-1 sm:flex-none text-center bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded shadow-sm font-bold transition-colors">
                                    Limpiar
                                </a>
                            </div>
                        </form>
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
                        <table class="w-full text-xs text-left border-collapse">
                            <thead class="bg-green-500 text-white font-bold whitespace-nowrap">
                                <tr>
                                    <th class="px-3 py-3 uppercase">Pedido N°</th>
                                    <th class="px-3 py-3 uppercase">Fecha Pedido</th>
                                    <th class="px-3 py-3 uppercase">Cotización N°</th>
                                    <th class="px-3 py-3 uppercase min-w-[150px]">Cliente</th>
                                    <th class="px-3 py-3 uppercase min-w-[150px]">Plantilla (Tipo)</th>
                                    @if(!auth()->user()->hasRole('Vendedor'))
                                    <th class="px-3 py-3 uppercase min-w-[120px]">Vendedor</th>
                                    @endif
                                    <th class="px-3 py-3 uppercase">Fecha Despacho</th>
                                    <th class="px-3 py-3 text-center uppercase">Estado Actual</th>
                                    <th class="px-3 py-3 text-center uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($pedidos as $p)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-2 py-2 font-bold text-gray-900 whitespace-nowrap">{{ $p->numero ?? 'N/A' }}</td>
                                        <td class="px-2 py-2 text-gray-600 whitespace-nowrap">{{ $p->fecha_confirmacion ? \Carbon\Carbon::parse($p->fecha_confirmacion)->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td class="px-2 py-2 text-gray-600 whitespace-nowrap">{{ $p->cotizacion->numero ?? 'N/A' }}</td>
                                        <td class="px-2 py-2 text-gray-600">{{ $p->cotizacion->cliente->nombre ?? '...' }}</td>
                                        <td class="px-2 py-2 text-gray-600 font-bold">{{ $p->cotizacion->plantilla->nombre ?? '-' }}</td>
                                        @if(!auth()->user()->hasRole('Vendedor'))
                                        <td class="px-2 py-2 text-gray-600">{{ $p->cotizacion->vendedor->name ?? 'N/A' }}</td>
                                        @endif
                                        <td class="px-2 py-2 whitespace-nowrap font-medium text-gray-700">
                                            @if($p->fecha_entrega_confirmada)
                                                {{ \Carbon\Carbon::parse($p->fecha_entrega_confirmada)->format('d/m/Y') }}
                                            @else
                                                <span class="text-gray-400 italic">Por confirmar</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <span class="px-2 inline-flex text-[10px] leading-5 font-semibold rounded-full 
                                                @if($p->estado == 'Pendiente') bg-yellow-100 text-yellow-800 
                                                @elseif($p->estado == 'Ajustado por Logística') bg-orange-100 text-orange-800
                                                @elseif($p->estado == 'Aprobado') bg-green-100 text-green-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ $p->estado }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 flex flex-wrap items-center justify-center gap-1 min-w-[200px]">
                                             <a href="{{ route('pedidos.show', $p->numero ?? $p->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-[10px] transition-colors">Ver</a>
                                             <a href="{{ route('pedidos.pdf', $p->numero ?? $p->id) }}" class="bg-red-600 hover:bg-red-800 text-white px-3 py-1.5 rounded text-[10px] transition-colors inline-flex items-center gap-1">
                                                 <i class="fas fa-file-pdf"></i> PDF
                                             </a>
                                             
                                             @role('Supervisor')
                                                 @if($p->estado === 'Pendiente')
                                                     <form action="{{ route('pedidos.revertir_a_cotizacion', $p->numero ?? $p->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de que desea revertir este pedido a cotización? Esta acción eliminará el pedido.')">
                                                         @csrf
                                                         <button type="submit" class="bg-red-600 hover:bg-red-800 text-white px-3 py-1.5 rounded text-[10px] flex items-center gap-1 transition-colors">
                                                             <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                             </svg>
                                                             Retroceder a Cotización
                                                         </button>
                                                     </form>
                                                 @endif
                                             @endrole

                                             @hasanyrole('Logistico|Administrador')
                                                 @if($p->estado == 'Aprobado')
                                                     <a href="{{ route('pedidos.picking', $p->numero ?? $p->id) }}" class="bg-indigo-600 hover:bg-indigo-800 text-white px-3 py-1.5 rounded text-[10px] flex items-center gap-1 transition-colors">
                                                         <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                         </svg>
                                                         Picking
                                                     </a>
                                                 @endif
                                             @endhasanyrole
     
                                             @if(auth()->user()->hasAnyRole(['Logistico', 'Administrador']) && !in_array($p->estado, ['Entregado', 'Anulado', 'Cancelado por el cliente']))
                                             <form action="{{ route('pedidos.update_estado', $p->numero ?? $p->id) }}" method="POST" class="flex gap-1">
                                                 @csrf
                                                 <select name="estado" class="text-[10px] py-1 px-2 border-gray-300 rounded shadow-sm">
                                                     @php
                                                         $indiceActual = \App\Models\Pedido::indiceEstado($p->estado) !== false ? \App\Models\Pedido::indiceEstado($p->estado) : 0;
                                                         $estadosManuales = ['Pendiente', 'En Revisión', 'Despachado', 'Entregado'];
                                                         if (!in_array($p->estado, $estadosManuales)) { $estadosManuales[] = $p->estado; }
                                                     @endphp
                                                     @foreach(\App\Models\Pedido::ESTADOS_ORDEN as $estadoOpcion)
                                                         @php $indiceOpcion = \App\Models\Pedido::indiceEstado($estadoOpcion); @endphp
                                                         @if(in_array($estadoOpcion, $estadosManuales) && $indiceOpcion !== false && $indiceOpcion >= $indiceActual)
                                                             <option value="{{ $estadoOpcion }}" {{ $p->estado == $estadoOpcion ? 'selected' : '' }}>{{ $estadoOpcion }}</option>
                                                         @endif
                                                     @endforeach
                                                 </select>
                                                 <button type="submit" class="bg-fenix-green text-white px-2 py-1 rounded text-[10px] font-bold">OK</button>
                                             </form>
                                             @elseif(auth()->user()->hasRole('Vendedor'))
                                             <select disabled class="text-[10px] py-1 px-2 border-gray-300 rounded shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed">
                                                 <option selected>{{ $p->estado }}</option>
                                             </select>
                                             @endif
                                         </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ !auth()->user()->hasRole('Vendedor') ? 9 : 8 }}" class="px-6 py-4 text-sm text-center">No hay pedidos registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
