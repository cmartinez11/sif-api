<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle de Cotización') }} - <span class="text-fenix-gold">{{ $cotizacion->numero }}</span>
            </h2>
            <div class="flex gap-2">
                <!-- Dropdown Descargar -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left no-print">
                    <button type="button" @click="open = !open" class="bg-gray-800 text-white px-4 py-2 rounded shadow hover:bg-black inline-flex items-center gap-1.5 transition focus:outline-none">
                        <span>Descargar</span>
                        <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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
                         class="origin-top-right absolute right-0 mt-1 w-44 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 divide-y divide-gray-100 focus:outline-none" 
                         style="display: none;">
                        <div class="py-1">
                            <a href="{{ route('cotizaciones.pdf', $cotizacion) }}" target="_blank" @click="open = false" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 group flex items-center px-4 py-2 text-sm font-medium">
                                <svg class="mr-3 h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Descargar PDF
                            </a>
                            <a href="{{ route('cotizaciones.jpg', $cotizacion) }}" target="_blank" @click="open = false" class="text-gray-700 hover:bg-gray-100 hover:text-gray-900 group flex items-center px-4 py-2 text-sm font-medium">
                                <svg class="mr-3 h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Descargar JPG
                            </a>
                        </div>
                    </div>
                </div>
                <a href="{{ route('cotizaciones.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 transition no-print">
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            .no-print { display: none !important; }
            .bg-gray-100 { background-color: white !important; padding: 0 !important; }
            .shadow-2xl, .border { shadow: none !important; border: none !important; }
            .max-w-full, .lg\:max-w-5xl { 
                max-width: 100% !important; 
                width: 100% !important; 
                margin: 0 !important; 
                padding: 0 !important; 
            }
            body { background-color: white !important; }
        }
    </style>

    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-full lg:max-w-5xl mx-auto bg-white p-10 shadow-2xl border h-auto min-h-[29.7cm] pb-20">
            
            @include('components.cotizacion.header')

            <div class="mb-6 grid grid-cols-2 gap-8 border-b pb-6">
                <div>
                    <h3 class="font-bold text-fenix-green mb-2 underline">DATOS DEL CLIENTE</h3>
                    <p><strong>Razón Social:</strong> {{ $cotizacion->cliente->nombre }}</p>
                    <p><strong>RUC:</strong> {{ $cotizacion->cliente->ruc }}</p>
                    <p><strong>Dirección:</strong> {{ $cotizacion->cliente->direccion ?? 'N/A' }}</p>
                    <p><strong>Condición Pago:</strong> {{ $cotizacion->condicion_pago ?? $cotizacion->cliente->condicion_pago ?? 'Contado' }}</p>
                    
                    <div class="mt-4 p-3 bg-gray-50 border rounded text-xs">
                        <h4 class="font-bold text-gray-500 uppercase mb-2 tracking-widest border-b border-[#0CC954] pb-1">Resumen Logístico</h4>
                        <div class="space-y-1">
                            <div class="flex items-start">
                                <span class="w-32 shrink-0 font-bold text-gray-700 uppercase" style="font-size: 10px;">Agencia:</span>
                                <span class="flex-1 text-gray-600 break-words">{{ $cotizacion->agencia ?? '-' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-32 shrink-0 font-bold text-gray-700 uppercase" style="font-size: 10px;">Dir. Agencia:</span>
                                <span class="flex-1 text-gray-600 break-words">{{ $cotizacion->direccion_agencia ?? '-' }}</span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-32 shrink-0 font-bold text-gray-700 uppercase" style="font-size: 10px;">Observaciones:</span>
                                <span class="flex-1 italic text-gray-500 break-words">{{ $cotizacion->observaciones ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <h3 class="font-bold text-fenix-green mb-2 underline">INFORMACIÓN GENERAL</h3>
                    <p><strong>Fecha Emisión:</strong> {{ \Carbon\Carbon::parse($cotizacion->fecha_emision)->format('d/m/Y') }}</p>
                    <p><strong>Moneda:</strong> {{ strtoupper($cotizacion->moneda) }}</p>
                    @if($cotizacion->moneda == 'dolares')
                        <p><strong>T.C.:</strong> {{ number_format($cotizacion->tipo_cambio, 3) }}</p>
                    @endif
                    <p><strong>Atendido por:</strong> {{ $cotizacion->vendedor->name ?? 'Vendedor' }}</p>
                    <p><strong>Plantilla:</strong> {{ $cotizacion->plantilla->nombre }}</p>
                </div>
            </div>

            <div class="mb-8">
                <table class="w-full text-sm text-left border-collapse border">
                    <thead class="bg-[#0CC954] text-white">
                        <tr>
                            <th class="border px-4 py-2 text-center">ITEM</th>
                            <th class="border px-4 py-2">CÓDIGO</th>
                            <th class="border px-4 py-2">PRODUCTO</th>
                            <th class="border px-4 py-2 text-right">CANTIDAD</th>
                            <th class="border px-4 py-2 text-right">P. UNITARIO</th>
                            <th class="border px-4 py-2 text-right">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cotizacion->items->where('estado_item', '!=', 'Rechazado')->values() as $index => $item)
                        @php $dt = json_decode($item->campos_json); @endphp
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $index + 1 }}</td>
                            <td class="border px-4 py-2">{{ $item->producto->codigo ?? 'N/A' }}</td>
                            <td class="border px-4 py-2">{{ $item->producto->nombre ?? 'N/A' }}</td>
                            <td class="border px-4 py-2 text-right">
                                @php 
                                    $nombrePlantilla = strtolower($cotizacion->plantilla->nombre); 
                                @endphp

                                @if(str_contains($nombrePlantilla, 'universal') || str_contains($nombrePlantilla, 'bolsas de polipropileno'))
                                    {{ $dt->cantidad ?? 0 }} {{ $dt->unidad ?? $item->producto->unidad_medida ?? '' }}
                                @elseif(str_contains($nombrePlantilla, 'tratadas') || str_contains($nombrePlantilla, 'pets'))
                                    {{ $dt->cantidad_millar ?? 0 }} (x {{ $dt->fardo ?? 0 }} Fardos)
                                @elseif(str_contains($nombrePlantilla, 'kilos'))
                                    {{ $dt->cantidad_fardos ?? 0 }} Fardos ({{ $dt->total_kilos ?? 0 }} Kg)
                                @else
                                    {{ $dt->cantidad ?? 0 }}
                                @endif
                            </td>
                            <td class="border px-4 py-2 text-right">
                                {{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($item->precio_unitario, 5) }}
                            </td>
                            <td class="border px-4 py-2 text-right font-bold">
                                {{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($item->precio_total, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mb-10">
                <div class="w-1/3 bg-gray-50 border p-4 rounded shadow-sm">
                    <div class="flex justify-between py-1 text-gray-600">
                        <span>SUB TOTAL</span> 
                        <span>{{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($cotizacion->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-gray-600 border-b mb-1">
                        <span>IGV (18%)</span> 
                        <span>{{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($cotizacion->igv, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 bg-[#f0f0f0] font-bold px-2 text-lg">
                        <span>TOTAL</span> 
                        <span>{{ $cotizacion->moneda == 'soles' ? 'S/' : '$' }} {{ number_format($cotizacion->total, 2) }}</span>
                    </div>

                    @if($cotizacion->moneda == 'dolares' && $cotizacion->tipo_cambio > 0)
                        <div class="flex justify-between py-2 bg-[#0CC954] text-white font-bold px-2 text-md mt-1 rounded">
                            <span class="text-xs uppercase self-center">Monto en Soles</span> 
                            <span>S/ {{ number_format($cotizacion->total * $cotizacion->tipo_cambio, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @include('components.cotizacion.footer-banco')
        </div>
    </div>
</x-app-layout>

