<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Panel de Control - Logística') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-800">Bienvenid@, {{ auth()->user()->name }}</h3>
                        <p class="text-gray-600 mt-1">Rol: <span class="font-bold text-fenix-green">Logística</span></p>
                    </div>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Para Despachar Hoy -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border-l-4 border-blue-500 hover:shadow-xl transition-shadow">
                    <div class="px-6 py-5">
                        <p class="text-blue-500 text-xs font-bold uppercase tracking-wider">Para Despachar Hoy</p>
                        <h4 class="text-4xl font-black text-gray-800 mt-2">{{ $despachosHoy }}</h4>
                        <p class="text-gray-400 text-[10px] mt-1 italic">* Con fecha de despacho confirmada para hoy</p>
                    </div>
                </div>

                <!-- Pendientes de Picking -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border-l-4 border-yellow-500 hover:shadow-xl transition-shadow">
                    <div class="px-6 py-5">
                        <p class="text-yellow-600 text-xs font-bold uppercase tracking-wider">Pendientes de Picking</p>
                        <h4 class="text-4xl font-black text-gray-800 mt-2">{{ $pendientesPicking }}</h4>
                        <p class="text-gray-400 text-[10px] mt-1 italic">* Pedidos aprobados listos para preparar</p>
                    </div>
                </div>

                <!-- Backorders en Espera -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border-l-4 border-red-500 hover:shadow-xl transition-shadow">
                    <div class="px-6 py-5">
                        <p class="text-red-500 text-xs font-bold uppercase tracking-wider">Backorders en Espera</p>
                        <h4 class="text-4xl font-black text-gray-800 mt-2">{{ $backordersEspera }}</h4>
                        <p class="text-gray-400 text-[10px] mt-1 italic">* Pedidos pendientes con saldo de stock</p>
                    </div>
                </div>

                <!-- Entregados esta Semana -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border-l-4 border-green-500 hover:shadow-xl transition-shadow">
                    <div class="px-6 py-5">
                        <p class="text-green-500 text-xs font-bold uppercase tracking-wider">Entregados Semana</p>
                        <h4 class="text-4xl font-black text-gray-800 mt-2">{{ $entregadosSemana }}</h4>
                        <p class="text-gray-400 text-[10px] mt-1 italic">* Pedidos finalizados en la semana actual</p>
                    </div>
                </div>

                <!-- CARD 5: Cola de Producción (Replicated from Supervisor) -->
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

                <!-- CARD 6: Alertas de Ruptura (Replicated from Supervisor) -->
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

            <!-- Agenda de Despachos -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 flex justify-between items-center">
                    <h4 class="text-white text-lg font-bold flex items-center gap-2">
                        📅 Agenda de Próximos Despachos
                    </h4>
                    <a href="{{ route('pedidos.index') }}" class="text-gray-400 hover:text-white text-xs font-semibold underline">Ver todos los pedidos</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-gray-600 font-bold uppercase text-[11px]">Fecha Despacho</th>
                                <th class="px-6 py-3 text-gray-600 font-bold uppercase text-[11px]">Pedido</th>
                                <th class="px-6 py-3 text-gray-600 font-bold uppercase text-[11px]">Cliente</th>
                                <th class="px-6 py-3 text-gray-600 font-bold uppercase text-[11px]">Plantilla</th>
                                <th class="px-6 py-3 text-gray-600 font-bold uppercase text-[11px] text-center">Estado</th>
                                <th class="px-6 py-3 text-gray-600 font-bold uppercase text-[11px] text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($agendaDespachos as $pedido)
                                @php
                                    $esHoy = \Carbon\Carbon::parse($pedido->fecha_entrega_confirmada)->isToday();
                                @endphp
                                <tr class="{{ $esHoy ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50 transition-colors' }}">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold {{ $esHoy ? 'text-blue-700' : 'text-gray-800' }}">
                                                {{ \Carbon\Carbon::parse($pedido->fecha_entrega_confirmada)->format('d/m/Y') }}
                                            </span>
                                            @if($esHoy)
                                                <span class="text-[10px] font-black uppercase text-blue-600 tracking-tighter">¡DESPACHAR HOY!</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $pedido->numero }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $pedido->cotizacion->cliente->nombre ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $pedido->cotizacion->plantilla->nombre ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase
                                            @if($pedido->estado === 'Aprobado') bg-blue-100 text-blue-700
                                            @elseif($pedido->estado === 'Despachado') bg-indigo-100 text-indigo-700
                                            @elseif($pedido->estado === 'Pendiente') bg-yellow-100 text-yellow-700
                                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ $pedido->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('pedidos.show', $pedido->numero) }}" class="inline-block bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-bold hover:bg-gray-50 transition shadow-sm">
                                            Gestionar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                        No hay despachos programados en la agenda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
