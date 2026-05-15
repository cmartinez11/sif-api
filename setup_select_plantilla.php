<?php

$dirs = [
    __DIR__ . '/resources/views/cotizaciones',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$selectPlantilla = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Seleccionar Plantilla de Cotización') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-center text-lg font-bold text-gray-700 mb-6">Elige el tipo de cotización a crear</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($plantillas as $p)
                        <a href="{{ route('cotizaciones.create', ['plantilla_id' => $p->id]) }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-fenix-green hover:text-white transition group">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-fenix-green group-hover:text-white">{{ $p->nombre }}</h5>
                            <p class="font-normal text-gray-700 group-hover:text-gray-200">Plantilla de cotización estándar para formato {{ $p->nombre }}.</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$createCotizacion = <<<'EOD'
<x-app-layout>
    <div x-data="cotizacionForm()" class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-[21cm] mx-auto bg-white p-8 shadow-2xl border" style="min-height: 29.7cm;">
            <form action="{{ route('cotizaciones.store') }}" method="POST" id="form-cotizacion">
                @csrf
                <input type="hidden" name="plantilla_id" value="{{ $plantilla->id }}">
                
                @include('components.cotizacion.header')

                <!-- Seleccionar Cliente para Cotizacion -->
                <div class="mb-4 no-print border p-4 bg-yellow-50 flex items-center gap-4">
                    <label class="font-bold shrink-0">Seleccione el Cliente:</label>
                    <select name="cliente_id" x-model="cliente_id" @change="fetchCliente" class="flex-grow border-gray-300 rounded shadow-sm">
                        <option value="">- Seleccionar Cliente -</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" data-ruc="{{ $c->ruc }}" data-dir="{{ $c->direccion }}" data-pago="{{ $c->condicion_pago }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                    
                    <label class="font-bold shrink-0 ml-4">Moneda:</label>
                    <select name="moneda" x-model="moneda" class="border-gray-300 rounded shadow-sm">
                        <option value="soles">Soles (S/)</option>
                        <option value="dolares">Dólares ($)</option>
                    </select>
                </div>

                @include('components.cotizacion.cliente-info')

                <!-- TABLA -->
                <div class="mb-4">
                    @if($plantilla->nombre == 'Tratadas')
                        @include('components.cotizacion.tabla-tratadas')
                    @elseif($plantilla->nombre == 'PPS')
                        @include('components.cotizacion.tabla-pps')
                    @elseif($plantilla->nombre == 'PETS')
                        @include('components.cotizacion.tabla-pets')
                    @else
                        @include('components.cotizacion.tabla-universal')
                    @endif
                </div>
                
                <div class="mb-6">
                    <button type="button" @click="addItem" class="text-sm bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded shadow">+ Añadir Fila</button>
                </div>

                <!-- TOTALES -->
                <div class="flex justify-end mb-6">
                    <div class="w-1/3 bg-gray-50 border p-2">
                        <div class="flex justify-between py-1"><span>SUB TOTAL</span> <span x-text="totals.subtotal"></span></div>
                        <div class="flex justify-between py-1"><span>IGV 18%</span> <span x-text="totals.igv"></span></div>
                        <div class="flex justify-between py-1 bg-fenix-gold font-bold p-1"><span>TOTAL</span> <span x-text="totals.total"></span></div>
                    </div>
                </div>

                @include('components.cotizacion.footer-banco')

                <div class="mt-8 text-center pt-8 border-t no-print">
                    <button type="button" @click="submitForm" class="bg-[#1a472a] hover:bg-green-900 text-white font-bold py-3 px-8 rounded-lg shadow-lg text-xl">
                        Guardar Cotización
                    </button>
                    <!-- Campo escondido para JSON payload -->
                    <input type="hidden" name="itemsJson" :value="JSON.stringify(items)">
                    <input type="hidden" name="subtotal" :value="totals.subtotalRaw">
                    <input type="hidden" name="igv" :value="totals.igvRaw">
                    <input type="hidden" name="total_final" :value="totals.totalRaw">
                </div>
            </form>
        </div>
    </div>

    <!-- Alpine Logic -->
    <script>
        function cotizacionForm() {
            return {
                cliente_id: '',
                moneda: 'soles',
                items: [ { codigo: '', producto_id: '', cantidad_millar: 0, fardo: 0, total_millares: 0, precio_unitario: 0, precio_total: 0 } ],
                
                addItem() {
                    this.items.push({ codigo: '', producto_id: '', cantidad_millar: 0, fardo: 0, total_millares: 0, precio_unitario: 0, precio_total: 0 });
                },
                removeItem(index) {
                    if(this.items.length > 1) this.items.splice(index, 1);
                },
                updateProductData(index) {
                    const sel = event.target;
                    const opt = sel.options[sel.selectedIndex];
                    this.items[index].codigo = opt.getAttribute('data-codigo');
                    this.items[index].precio_unitario = parseFloat(opt.getAttribute('data-precio') || 0);
                    this.calculateRow(index);
                },
                calculateRow(index) {
                    const i = this.items[index];
                    i.total_millares = (i.cantidad_millar || 0) * (i.fardo || 0);
                    i.precio_total = i.total_millares * (i.precio_unitario || 0);
                },
                fetchCliente() {
                    // This updates visual info dynamically if needed
                },
                get totals() {
                    let st = 0;
                    this.items.forEach(i => st += (Number(i.precio_total) || 0));
                    let igv = st * 0.18;
                    let tot = st + igv;
                    let symbol = this.moneda === 'soles' ? 'S/ ' : '$ ';
                    return {
                        subtotalRaw: st, igvRaw: igv, totalRaw: tot,
                        subtotal: symbol + st.toFixed(2),
                        igv: symbol + igv.toFixed(2),
                        total: symbol + tot.toFixed(2)
                    }
                },
                submitForm() {
                    if(!this.cliente_id) return alert('Seleccione un cliente');
                    document.getElementById('form-cotizacion').submit();
                }
            }
        }
    </script>
</x-app-layout>
EOD;

file_put_contents(__DIR__ . '/resources/views/cotizaciones/select_plantilla.blade.php', $selectPlantilla);
file_put_contents(__DIR__ . '/resources/views/cotizaciones/create.blade.php', $createCotizacion);
echo "Views generated correctly.\n";
