<?php

$dirPedidos = __DIR__ . '/resources/views/pedidos';
if (!is_dir($dirPedidos)) mkdir($dirPedidos, 0777, true);

$cotizacionesIndex = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Gestión de Cotizaciones') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Cotizaciones Recientes</h3>
                        <a href="{{ route('cotizaciones.create') }}" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-4 rounded shadow">
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

                    <table class="min-w-full divide-y divide-gray-200 mt-4">
                        <thead class="bg-fenix-green">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plantilla</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($cotizaciones as $c)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $c->numero }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $c->cliente->nombre }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $c->plantilla->nombre }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $c->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($c->total, 2) }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $c->estado == 'Borrador' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $c->estado == 'Convertida a Pedido' ? 'bg-green-100 text-green-800' : '' }}
                                        ">
                                            {{ $c->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm flex gap-2">
                                        <a href="{{ route('cotizaciones.pdf', $c) }}" target="_blank" class="bg-gray-800 text-white px-2 py-1 rounded text-xs hover:bg-black">PDF</a>
                                        @if($c->estado !== 'Convertida a Pedido')
                                            <form action="{{ route('pedidos.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="cotizacion_id" value="{{ $c->id }}">
                                                <button type="submit" class="bg-fenix-gold text-black font-bold px-2 py-1 rounded text-xs hover:bg-yellow-500">Confirmar a Pedido</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-sm text-center">No hay cotizaciones.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$pedidosIndex = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Seguimiento de Pedidos') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3 mb-6">Listado de Pedidos</h4>
                    
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

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#1a472a]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cotización N°</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plantilla (Tipo)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Vendedora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado Actual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actualizar</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($pedidos as $p)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $p->cotizacion->numero }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $p->cotizacion->cliente->nombre }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-bold">{{ $p->cotizacion->plantilla->nombre }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $p->cotizacion->vendedora->name }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $p->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if(auth()->user()->hasAnyRole(['Logistico', 'Supervisor', 'Administrador']))
                                        <form action="{{ route('pedidos.update_estado', $p) }}" method="POST" class="flex gap-2">
                                            @csrf
                                            <select name="estado" class="text-xs border-gray-300 rounded shadow-sm">
                                                <option value="Pendiente" {{ $p->estado == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                <option value="En Proceso" {{ $p->estado == 'En Proceso' ? 'selected' : '' }}>En Proceso</option>
                                                <option value="Despachado" {{ $p->estado == 'Despachado' ? 'selected' : '' }}>Despachado</option>
                                                <option value="Entregado" {{ $p->estado == 'Entregado' ? 'selected' : '' }}>Entregado</option>
                                            </select>
                                            <button type="submit" class="bg-fenix-green text-white px-2 py-1 rounded text-xs">Guardar</button>
                                        </form>
                                        @else
                                            <span class="text-gray-400 text-xs text-italic">Sin acceso a editar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-4 text-sm text-center">No hay pedidos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

file_put_contents(__DIR__ . '/resources/views/cotizaciones/index.blade.php', $cotizacionesIndex);
file_put_contents(__DIR__ . '/resources/views/pedidos/index.blade.php', $pedidosIndex);

echo "list views generated.";

