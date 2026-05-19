<x-app-layout>
    <!-- jQuery y Select2 via CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Cotización') }} - <span class="text-fenix-gold">{{ $cotizacione->numero }}</span>
            </h2>
            <a href="{{ route('cotizaciones.index') }}" class="text-sm bg-gray-500 text-white px-3 py-1 rounded">
                Volver
            </a>
        </div>
    </x-slot>

    @php
    $initialItems = $cotizacione->items->map(function($item) {
    $fields = json_decode($item->campos_json, true);
    return array_merge([
    'producto_id' => $item->producto_id,
    'codigo' => $item->producto->codigo ?? '',
    'precio_unitario' => $item->precio_unitario,
    'precio_total' => $item->precio_total,
    'estado_item' => $item->estado_item ?? 'Activo',
    'motivo_rechazo' => $item->motivo_rechazo ?? '',
    'precio_competencia' => $item->precio_competencia ?? '',
    ], $fields);
    });
    @endphp

    <div x-data="cotizacionForm({{ $initialItems->toJson() }})" class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-[98%] mx-auto bg-white p-10 shadow-xl border border-[#e5e7eb] rounded-xl h-auto min-h-[29.7cm] pb-20">

            <form action="{{ route('cotizaciones.update', $cotizacione) }}" method="POST" id="form-cotizacion">
                @csrf
                @method('PUT')

                @include('components.cotizacion.header')

                <!-- SECCIÓN DE CABECERA REDISEÑADA -->
                <div class="mb-8 p-6 bg-white border border-[#e5e7eb] rounded-lg shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Contacto:</label>
                            <select x-model="contacto_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                                <option value="">- Seleccionar Contacto -</option>
                                @foreach($contactos as $con)
                                <option value="{{ $con->id }}">{{ $con->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Razon Social:</label>
                            <select name="cliente_id" x-model="cliente_id" @change="updateClienteData()" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                                <option value="">- Seleccionar Cliente -</option>
                                <template x-for="c in filteredClientes" :key="c.id">
                                    <option :value="c.id" x-text="c.nombre"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Agencia:</label>
                            <input type="text" name="agencia" x-model="agencia" placeholder="Digitar agencia" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Condición de Pago</label>
                            <select name="condicion_pago_cotizacion" x-model="condicion_pago_cotizacion" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                                <option value="CONTADO">CONTADO</option>
                                <option value="7 DIAS">7 DIAS</option>
                                <option value="10 DIAS">10 DIAS</option>
                                <option value="15 DIAS">15 DIAS</option>
                                <option value="20 DIAS">20 DIAS</option>
                                <option value="30 DIAS">30 DIAS</option>
                                <option value="45 DIAS">45 DIAS</option>
                                <option value="60 DIAS">60 DIAS</option>
                                <option value="90 DIAS">90 DIAS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Fecha de Entrega Estimada:</label>
                            <input type="date" name="fecha_entrega_estimada" x-model="fecha_entrega_estimada" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Vendedor de Campo:</label>
                            <select name="vendedor_campo_id" x-model="vendedor_campo_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                                <option value="">- Seleccionar Vendedor (Opcional) -</option>
                                @foreach($vendedoresCampo as $vendedor)
                                <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Dirección agencia:</label>
                            <input type="text" name="direccion_agencia" x-model="direccion_agencia" placeholder="Digitar dirección" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Observaciones:</label>
                            <textarea name="observaciones" x-model="observaciones" placeholder="Digitar observaciones" rows="1" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]"></textarea>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-6">
                        <div class="w-48">
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Moneda:</label>
                            <select name="moneda" x-model="moneda" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                                <option value="soles">Soles (S/)</option>
                                <option value="dolares">Dólares ($)</option>
                            </select>
                        </div>
                        <div class="flex-grow max-w-xs" x-show="moneda === 'dolares'" x-cloak>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Tipo de cambio:</label>
                            <input type="number" step="0.001" name="tipo_cambio" x-model="tipo_cambio" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                        </div>
                        <div class="text-xs text-gray-500 italic mt-6" x-show="moneda === 'dolares'" x-cloak>
                            * Este dato solo se muestra si en moneda se selecciona dólares.
                        </div>
                    </div>
                </div>

                @include('components.cotizacion.cliente-info')

                <div class="mb-4 mt-6">
                    @if($plantilla->nombre == 'Tratadas')
                    @include('components.cotizacion.tabla-tratadas')
                    @elseif($plantilla->nombre == 'Bolsas de Polipropileno')
                    @include('components.cotizacion.tabla-pps')
                    @elseif($plantilla->nombre == 'Pets')
                    @include('components.cotizacion.tabla-pets')
                    @elseif($plantilla->nombre == 'Bolsas de Polipropileno por kilos')
                    @include('components.cotizacion.tabla-polipropileno-x-kilos')
                    @else
                    @include('components.cotizacion.tabla-universal')
                    @endif
                </div>

                <div class="mb-6 no-print">
                    <button type="button" @click="addItem" class="text-sm bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded shadow transition">
                        + Añadir Fila
                    </button>
                </div>

                <div class="flex justify-between items-start mb-6 gap-8">
                    <!-- RESUMEN LOGÍSTICO -->
                    <div class="w-1/2 p-4 bg-white border border-dashed border-gray-300 rounded-lg">
                        <h4 class="text-xs font-bold text-gray-400 uppercase mb-3 tracking-widest">Resumen Logístico</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-start border-b border-gray-50 pb-1">
                                <span class="w-24 shrink-0 font-semibold text-[#0CC954]">Agencia:</span>
                                <span class="flex-1 text-gray-700 break-words" x-text="agencia || '-'"></span>
                            </div>
                            <div class="flex items-start border-b border-gray-50 pb-1">
                                <span class="w-24 shrink-0 font-semibold text-[#0CC954]">Dirección:</span>
                                <span class="flex-1 text-gray-700 break-words" x-text="direccion_agencia || '-'"></span>
                            </div>
                            <div class="flex items-start border-b border-gray-50 pb-1">
                                <span class="w-24 shrink-0 font-semibold text-[#0CC954]">Entrega:</span>
                                <span class="flex-1 text-gray-700 break-words" x-text="fecha_entrega_estimada || '-'"></span>
                            </div>
                            <div class="flex items-start">
                                <span class="w-24 shrink-0 font-semibold text-[#0CC954]">Obs:</span>
                                <span class="flex-1 text-gray-700 italic break-words" x-text="observaciones || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- TOTALES -->
                    <div class="w-1/3 bg-white border border-[#e5e7eb] rounded-lg shadow-sm overflow-hidden">
                        <div class="flex justify-between p-3 text-gray-600">
                            <span class="font-bold uppercase text-xs">SUB TOTAL</span>
                            <span x-text="totals.subtotal"></span>
                        </div>
                        <div class="flex justify-between p-3 text-gray-600 border-t border-gray-50">
                            <span class="font-bold uppercase text-xs">IGV (18%)</span>
                            <span x-text="totals.igv"></span>
                        </div>
                        <div class="flex justify-between p-4 bg-gray-50 border-t border-gray-200 text-[#0CC954]">
                            <span class="font-black text-xl">TOTAL</span>
                            <span class="font-black text-xl" x-text="totals.total"></span>
                        </div>
                        <!-- FILA EXTRA SI ES DÓLARES -->
                        <div class="p-4 bg-[#0CC954] text-white flex justify-between items-center" x-show="moneda === 'dolares' && tipo_cambio > 0" x-cloak>
                            <div class="text-[10px] font-bold uppercase leading-tight">
                                Monto en Soles <br>
                                <span class="text-fenix-gold" x-text="'T.C. ' + tipo_cambio"></span>
                            </div>
                            <span class="font-black text-xl" x-text="'S/ ' + totals.totalInSoles"></span>
                        </div>
                    </div>
                </div>

                @include('components.cotizacion.footer-banco')

                <div class="mt-8 text-center pt-8 border-t no-print">
                    <button type="button" @click="submitForm" class="bg-[#0CC954] hover:bg-green-900 text-white font-bold py-3 px-10 rounded-lg shadow-lg text-xl transition transform hover:scale-105">
                        Actualizar Cotización
                    </button>

                    <input type="hidden" name="itemsJson" :value="JSON.stringify(items)">
                    <input type="hidden" name="subtotal" :value="totals.subtotalRaw">
                    <input type="hidden" name="igv" :value="totals.igvRaw">
                    <input type="hidden" name="total_final" :value="totals.totalRaw">
                </div>
            </form>
        </div>

        @include('crm.modal_competencia')
        @include('components.cotizacion.modal-rechazo')
        @include('components.cotizacion.modal-perdida-item')
    </div>

    <script>
        function cotizacionForm(initialItems) {
            return {
                init() {
                    this.$nextTick(() => {
                        $('.select-producto').select2({ 
                            placeholder: 'Seleccione...',
                            minimumResultsForSearch: 0,
                            width: '100%'
                        });
                    });
                },
                contacto_id: @json($cotizacione->cliente->contacto_id ?? ''),
                cliente_id: @json($cotizacione->cliente_id),
                condicion_pago_cotizacion: @json($cotizacione->condicion_pago ?? $cotizacione->cliente->condicion_pago ?? 'CONTADO'),
                agencia: @json($cotizacione->agencia),
                direccion_agencia: @json($cotizacione->direccion_agencia),
                fecha_entrega_estimada: @json($cotizacione->fecha_entrega_estimada ? $cotizacione->fecha_entrega_estimada->format('Y-m-d') : ''),
                vendedor_campo_id: @json($cotizacione->vendedor_campo_id),
                observaciones: @json($cotizacione->observaciones ?? ''),
                allClientes: @json($clientes),
                moneda: @json($cotizacione->moneda),
                tipo_cambio: @json($cotizacione->tipo_cambio ?? 1),
                // Estado del cliente seleccionado para reactividad
                cliente: {
                    nombre: @json($cotizacione->cliente->nombre),
                    ruc: @json($cotizacione->cliente->ruc),
                    direccion: @json($cotizacione->cliente->direccion),
                    condicion_pago: @json($cotizacione->cliente->condicion_pago),
                    provincia: @json($cotizacione->cliente->provincia)
                },
                items: initialItems || [],
                modalRechazoOpen: false,
                rechazoIndex: null,
                rechazoItemData: { motivo_rechazo: '', precio_competencia: '' },
                
                // Modal de Pérdida de Ítem (X)
                modalPerdidaOpen: false,
                perdidaIndex: null,
                perdidaData: {
                    proveedor_nombre: '',
                    motivo_perdida: '',
                    precio_ofrecido: '',
                    entrega_proveedor: '',
                    entrega_nuestra: '',
                    detalle_perdida: ''
                },

                addItem() {
                    this.items.push({
                        producto_id: '',
                        codigo: '',
                        cantidad: '',
                        cantidad_millar: '',
                        fardo: '',
                        total_kilos: '',
                        total_millares: '',
                        precio_unitario: '',
                        precio_total: '',
                        unidad_medida: '',
                        estado_item: 'Activo',
                        motivo_rechazo: '',
                        precio_competencia: ''
                    });

                    // Re-inicializar Select2 en la nueva fila
                    this.$nextTick(() => {
                        $('.select-producto').select2({ 
                            placeholder: 'Seleccione...',
                            minimumResultsForSearch: 0,
                            width: '100%'
                        });
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                updateProductData(index, event) {
                    const sel = event.target;
                    const opt = sel.options[sel.selectedIndex];

                    if (opt.value === "") {
                        this.items[index].codigo = '';
                        this.items[index].precio_unitario = 0;
                    } else {
                        this.items[index].codigo = opt.getAttribute('data-codigo');
                        this.items[index].precio_unitario = parseFloat(opt.getAttribute('data-precio') || 0);
                        this.items[index].unidad_medida = opt.getAttribute('data-unidad') || '-';
                    }
                    this.calculateRow(index);
                },

                calculateRow(index) {
                    const i = this.items[index];
                    const nombrePlantilla = "{{ $plantilla->nombre }}";

                    if (nombrePlantilla === 'Universal') {
                        // Cálculo directo para la Universal: cantidad * precio_unitario = total
                        i.precio_total = (parseFloat(i.cantidad) || 0) * (parseFloat(i.precio_unitario) || 0);
                    } else if (nombrePlantilla === 'Bolsas de Polipropileno') {
                        // Lógica para PPS: cantidad * fardo = total_kilos; total_kilos * precio_unitario = total
                        i.total_kilos = (parseFloat(i.cantidad) || 0) * (parseFloat(i.fardo) || 0);
                        i.precio_total = i.total_kilos * (parseFloat(i.precio_unitario) || 0);
                    } else if (nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                        // Lógica para PP Kilos: total_kilos * precio_unitario = total
                        i.precio_total = (parseFloat(i.total_kilos) || 0) * (parseFloat(i.precio_unitario) || 0);
                    } else {
                        // Lógica para Tratadas/PETS: cantidad_millar * fardo = total_millares; total_millares * precio_unitario = total
                        i.total_millares = (parseFloat(i.cantidad_millar) || 0) * (parseFloat(i.fardo) || 0);
                        i.precio_total = i.total_millares * (parseFloat(i.precio_unitario) || 0);
                    }
                },

                get filteredClientes() {
                    if (!this.contacto_id) return [];
                    return this.allClientes.filter(c => c.contacto_id == this.contacto_id);
                },

                updateClienteData() {
                    const client = this.allClientes.find(c => c.id == this.cliente_id);
                    if (client) {
                        this.cliente.nombre = client.nombre;
                        this.cliente.ruc = client.ruc || 'No definido';
                        this.cliente.direccion = client.direccion || 'No definido';
                        this.cliente.condicion_pago = client.condicion_pago || 'No definido';
                        this.condicion_pago_cotizacion = client.condicion_pago || 'CONTADO';
                        this.cliente.provincia = client.provincia || '';
                    } else {
                        this.cliente = {
                            nombre: '',
                            ruc: '',
                            direccion: '',
                            condicion_pago: '',
                            provincia: ''
                        };
                        this.condicion_pago_cotizacion = 'CONTADO';
                    }
                },
                get totals() {
                    let tot = 0;
                    this.items.forEach(i => {
                        if (i.estado_item !== 'Rechazado') {
                            tot += (parseFloat(i.precio_total) || 0);
                        }
                    });

                    let st = tot / 1.18;
                    let igv = tot - st;
                    let symbol = this.moneda === 'soles' ? 'S/ ' : '$ ';

                    let totSoles = (tot * (parseFloat(this.tipo_cambio) || 0)).toFixed(2);

                    return {
                        subtotalRaw: st.toFixed(2),
                        igvRaw: igv.toFixed(2),
                        totalRaw: tot.toFixed(2),
                        subtotal: symbol + st.toLocaleString('en-US', {
                            minimumFractionDigits: 2
                        }),
                        igv: symbol + igv.toLocaleString('en-US', {
                            minimumFractionDigits: 2
                        }),
                        total: symbol + tot.toLocaleString('en-US', {
                            minimumFractionDigits: 2
                        }),
                        totalInSoles: parseFloat(totSoles).toLocaleString('en-US', {
                            minimumFractionDigits: 2
                        })
                    }
                },

                submitForm() {
                    // 1. VALIDACIÓN: Evita enviar si no hay cliente
                    if (!this.cliente_id) {
                        return alert('Por favor, seleccione un cliente antes de continuar.');
                    }

                    // 2. SINCRONIZACIÓN CRÍTICA: 
                    // Alpine.js tiene los nuevos productos en "this.items", 
                    // pero tenemos que pasarlos al input real que PHP va a leer.
                    document.getElementsByName('itemsJson')[0].value = JSON.stringify(this.items);

                    // 3. ACTUALIZACIÓN DE TOTALES:
                    // Pasamos los números limpios (sin S/ ni comas) a los campos ocultos
                    document.getElementsByName('subtotal')[0].value = this.totals.subtotalRaw;
                    document.getElementsByName('igv')[0].value = this.totals.igvRaw;
                    document.getElementsByName('total_final')[0].value = this.totals.totalRaw;

                    // 4. ENVÍO AL CONTROLADOR
                    if (confirm('¿Desea actualizar esta cotización?')) {
                        document.getElementById('form-cotizacion').submit();
                    }
                },

                abrirModalRechazo(index) {
                    this.rechazoIndex = index;
                    this.rechazoItemData = { motivo_rechazo: '', precio_competencia: '' };
                    this.modalRechazoOpen = true;
                },
                cerrarModalRechazo() {
                    this.modalRechazoOpen = false;
                    this.rechazoIndex = null;
                    // Limpiar campos automáticamente al cerrar
                    this.rechazoItemData = { motivo_rechazo: '', precio_competencia: '' };
                },
                confirmarRechazo() {
                    if (!this.rechazoItemData.motivo_rechazo) {
                        alert('El motivo de rechazo es obligatorio.');
                        return;
                    }
                    if (this.rechazoIndex !== null) {
                        this.items[this.rechazoIndex].estado_item = 'Rechazado';
                        this.items[this.rechazoIndex].motivo_rechazo = this.rechazoItemData.motivo_rechazo;
                        this.items[this.rechazoIndex].precio_competencia = this.rechazoItemData.precio_competencia;
                    }
                    this.cerrarModalRechazo();
                },
                deshacerRechazo(index) {
                    this.items[index].estado_item = 'Activo';
                    this.items[index].motivo_rechazo = '';
                    this.items[index].precio_competencia = '';
                },

                // Lógica de Pérdida de Ítem
                abrirModalPerdida(index) {
                    this.perdidaIndex = index;
                    this.perdidaData = {
                        proveedor_nombre: '',
                        motivo_perdida: '',
                        precio_ofrecido: '',
                        entrega_proveedor: '',
                        entrega_nuestra: '',
                        detalle_perdida: ''
                    };
                    this.modalPerdidaOpen = true;
                },
                cerrarModalPerdida() {
                    this.modalPerdidaOpen = false;
                    this.perdidaIndex = null;
                },
                confirmarPerdida() {
                    if (!this.perdidaData.proveedor_nombre || !this.perdidaData.motivo_perdida) {
                        alert('Nombre de competencia y motivo son obligatorios.');
                        return;
                    }
                    if (this.perdidaIndex !== null) {
                        // Marcamos como rechazado para que no sume y guardamos los datos extra
                        let item = this.items[this.perdidaIndex];
                        item.estado_item = 'Rechazado';
                        item.oculto = true;
                        item.motivo_rechazo = this.perdidaData.proveedor_nombre; // Reusamos para el controlador
                        item.perdida_data = JSON.parse(JSON.stringify(this.perdidaData)); // Guardamos todo el objeto
                    }
                    this.cerrarModalPerdida();
                }
            }
        }
    </script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .shadow-2xl {
                shadow: none !important;
                border: none !important;
            }
        }

        /* Estilos personalizados para Select2 - Branding Fénix */
        .select2-container--default .select2-selection--single {
            border-radius: 0.5rem;
            height: 42px;
            border-color: #d1d5db;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: #0CC954;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-dropdown {
            border-radius: 0.5rem;
            border-color: #e5e7eb;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 9999;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0CC954;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151;
            font-size: 0.875rem;
            padding-left: 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6b7280;
        }
    </style>

</x-app-layout>