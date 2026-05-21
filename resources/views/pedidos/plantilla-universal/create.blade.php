<x-app-layout>
    <!-- jQuery y Select2 via CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <h2 class="font-semibold text-lg md:text-xl text-gray-800 leading-tight">
                {{ __('Nuevo Pedido Directo') }} - <span class="text-fenix-gold">{{ $plantilla->nombre }}</span>
            </h2>
            <a href="{{ route('pedidos.index') }}" class="w-full sm:w-auto text-center text-sm bg-gray-500 text-white px-3 py-2 sm:py-1 rounded shadow hover:bg-gray-600 transition">
                Volver
            </a>
        </div>
    </x-slot>

    <div x-data="pedidoForm()"
        x-init="
            $nextTick(() => {
                $('#select-contacto').select2({ placeholder: '- Seleccionar Contacto -' }).on('change', (e) => {
                    contacto_id = e.target.value;
                });

                $('#select-cliente').select2({ placeholder: '- Seleccionar Cliente -' }).on('change', (e) => {
                    cliente_id = e.target.value;
                    updateClienteData();
                });
            });

            $watch('filteredClientes', (value) => {
                $nextTick(() => {
                    $('#select-cliente').select2({ placeholder: '- Seleccionar Cliente -' });
                });
            });
         "
        class="relative bg-gray-100 min-h-screen py-8">

        <div class="max-w-full lg:max-w-[98%] mx-auto bg-white p-4 md:p-10 shadow-xl border border-[#e5e7eb] rounded-xl h-auto min-h-[29.7cm] pb-20">

            <form action="{{ route('pedidos.store_directo', 'universal') }}" method="POST" id="form-pedido">
                @csrf

                <!-- SECCIÓN DE CABECERA PERSONALIZADA PARA PEDIDO DIRECTO -->
                <div class="flex justify-between items-start mb-6 border-b pb-4">
                    <div class="flex items-center">
                        <img src="/logo2.png" alt="Logo" style="width:180px; height: auto; object-fit: contain;" />
                    </div>
                    <div class="text-right">
                        <h1 class="text-3xl font-bold text-[#0CC954] tracking-wider">PEDIDO DIRECTO</h1>
                        <p class="text-lg text-gray-700 font-semibold mt-2">N° - NUEVO (PENDIENTE)</p>
                    </div>
                </div>
                
                <div class="bg-[#0CC954] text-white p-3 rounded-t-lg flex justify-between">
                    <div>
                        <p class="font-bold">PLASTICOS FENIX EIRL</p>
                        <p class="font-bold">RUC: 20522086704</p>
                        <p class="text-sm">Jr. Loreto Posesionarios Mypes de Villa Sol. Jicamarca Lima - Huarochirí</p>
                    </div>
                    <div class="text-right flex flex-col justify-center">
                        <p class="text-sm flex items-center justify-end"><span class="mr-2">📅</span> FECHA DE EMISIÓN</p>
                        <p class="font-bold">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>
                    </div>
                </div>

                <!-- SECCIÓN DE CABECERA REDISEÑADA -->
                <div class="mb-8 p-6 bg-white border border-[#e5e7eb] rounded-b-lg shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Contacto:</label>
                            <select id="select-contacto" x-model="contacto_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954]">
                                <option value="">- Seleccionar Contacto -</option>
                                @foreach($contactos as $con)
                                <option value="{{ $con->id }}">{{ $con->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <label class="block text-sm font-bold text-[#0CC954] uppercase tracking-wide">Razon Social:</label>
                            </div>
                            <select id="select-cliente" 
                                    name="cliente_id" 
                                    x-model="cliente_id" 
                                    class="w-full border-gray-300 rounded-lg shadow-sm">
                                <option value="">- Seleccionar o Buscar Cliente -</option>
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

                    <div class="flex flex-col md:flex-row flex-wrap items-end gap-4 md:gap-6">
                        <div class="w-full md:w-48">
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Moneda:</label>
                            <select name="moneda" x-model="moneda" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954] py-3 md:py-2">
                                <option value="soles">Soles (S/)</option>
                                <option value="dolares">Dólares ($)</option>
                            </select>
                        </div>
                        <div class="w-full md:flex-grow md:max-w-xs" x-show="moneda === 'dolares'" x-cloak>
                            <label class="block text-sm font-bold text-[#0CC954] mb-2 uppercase tracking-wide">Tipo de cambio:</label>
                            <input type="number" step="0.001" name="tipo_cambio" x-model="tipo_cambio" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#0CC954] focus:border-[#0CC954] py-3 md:py-2">
                        </div>
                        <div class="w-full md:w-auto text-xs text-gray-500 italic" x-show="moneda === 'dolares'" x-cloak>
                            * Este dato solo se muestra si en moneda se selecciona dólares.
                        </div>
                    </div>
                </div>

                @include('components.cotizacion.cliente-info')

                <!-- TABLA DE PRODUCTOS -->
                <div class="mb-4 mt-6">
                    @include('components.cotizacion.tabla-universal')
                </div>

                <div class="mb-6 no-print flex flex-col sm:flex-row flex-wrap gap-2">
                    <button type="button" @click="addItem" class="w-full sm:w-auto text-sm bg-gray-200 hover:bg-gray-300 px-4 py-3 sm:py-2 rounded shadow transition">
                        + Añadir Fila
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row justify-between items-start mb-6 gap-6">
                    <!-- RESUMEN LOGÍSTICO -->
                    <div class="w-full lg:w-1/2 p-4 bg-white border border-dashed border-gray-300 rounded-lg">
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
                            <div class="flex items-start">
                                <span class="w-24 shrink-0 font-semibold text-[#0CC954]">Obs:</span>
                                <span class="flex-1 text-gray-700 italic break-words" x-text="observaciones || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- TOTALES -->
                    <div class="w-full lg:w-1/3 bg-white border border-[#e5e7eb] rounded-lg shadow-sm overflow-hidden">
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
                    <button type="button" @click="submitForm" class="w-full md:w-auto bg-[#0CC954] hover:bg-green-900 text-white font-bold py-4 md:py-3 px-10 rounded-lg shadow-lg text-lg md:text-xl transition transform hover:scale-105">
                        Guardar Pedido Directo
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

        <!-- MODAL DE MONITOREO DE STOCK Y VENTAS -->
        <div x-show="openStockModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity"
             @keydown.escape.window="cerrarStockModal()">
            
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all duration-300 border border-gray-100 animate-fade-in-down"
                 x-show="openStockModal"
                 @click.away="cerrarStockModal()">
                
                <!-- CABECERA -->
                <div class="bg-gradient-to-r from-[#0CC954] to-emerald-500 px-6 py-4 flex justify-between items-center text-white">
                    <div>
                        <h3 class="text-lg font-extrabold tracking-tight" x-text="modalStockProduct.nombre"></h3>
                        <p class="text-xs text-green-100 font-semibold" x-text="'Cód: ' + modalStockProduct.codigo"></p>
                    </div>
                    <button type="button" @click="cerrarStockModal()" class="text-white hover:text-green-200 transition duration-150 p-1 rounded-full hover:bg-white/10">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- CONTENIDO -->
                <div class="p-6 space-y-6">
                    <!-- LOADING STATE -->
                    <div x-show="isStockLoading" class="flex flex-col items-center justify-center py-10 space-y-3">
                        <svg class="animate-spin h-10 w-10 text-[#0CC954]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-semibold text-gray-500">Consultando stock...</span>
                    </div>

                    <!-- DATA STATE -->
                    <div x-show="!isStockLoading" class="space-y-6" x-cloak>
                        <!-- STOCK CARD -->
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-5 shadow-sm text-center relative overflow-hidden">
                            <div class="absolute -right-6 -bottom-6 text-emerald-200/40 pointer-events-none">
                                <i class="fas fa-boxes text-7xl"></i>
                            </div>
                            <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest block mb-1">Stock Actual en Almacén</span>
                            <span class="text-3xl font-black text-emerald-600 font-mono tracking-tight" x-text="modalStockProduct.stock"></span>
                        </div>

                        <!-- LISTADO VENTAS -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-shopping-cart text-gray-400"></i>
                                Pedidos Confirmados Hoy por Vendedora
                            </h4>
                            
                            <!-- TABLA DE VENTAS -->
                            <div class="border border-gray-150 rounded-xl overflow-hidden shadow-sm" x-show="modalStockProduct.ventas_hoy.length > 0">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-gray-50 text-gray-600 border-b border-gray-150 text-xs font-bold uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3">Vendedora</th>
                                            <th class="px-4 py-3 text-right">Cantidad Vendida Hoy</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="venta in modalStockProduct.ventas_hoy" :key="venta.vendedora">
                                            <tr class="hover:bg-gray-50 transition duration-150">
                                                <td class="px-4 py-3 font-semibold text-gray-800" x-text="venta.vendedora"></td>
                                                <td class="px-4 py-3 text-right font-mono text-gray-900 font-bold" x-text="venta.cantidad"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- ALERTA SIN VENTAS -->
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center text-sm text-gray-500 font-semibold"
                                 x-show="modalStockProduct.ventas_hoy.length === 0">
                                Sin ventas registradas el día de hoy.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCIONES DE PIE -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                    <button type="button" @click="cerrarStockModal()" class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white font-bold py-2.5 px-6 rounded-lg text-sm transition duration-150 shadow-md">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function pedidoForm() {
            return {
                contacto_id: '',
                cliente_id: '',
                condicion_pago_cotizacion: 'CONTADO',
                agencia: '',
                direccion_agencia: '',
                fecha_entrega_estimada: '',
                vendedor_campo_id: '',
                observaciones: '',
                allClientes: @json($clientes),
                moneda: 'soles',
                tipo_cambio: 1,
                // Estado del cliente seleccionado para reactividad
                cliente: {
                    nombre: '',
                    ruc: '',
                    direccion: '',
                    condicion_pago: '',
                    provincia: ''
                },
                contacto: {
                    nombre: ''
                },
                // Modal de Monitoreo de Stock
                openStockModal: false,
                modalStockProduct: {
                    id: null,
                    codigo: '',
                    nombre: '',
                    stock: '0.000',
                    ventas_hoy: []
                },
                isStockLoading: false,
                // Inicializamos con una fila vacía
                items: [{
                    producto_id: '',
                    codigo: '',
                    nombre: '',
                    cantidad: '',
                    cantidad_millar: '',
                    fardo: '',
                    total_kilos: '',
                    total_millares: '',
                    precio_unitario: '',
                    precio_total: '',
                    unidad: '',
                    unidad_medida: '',
                    estado_item: 'Activo',
                    motivo_rechazo: '',
                    precio_competencia: ''
                }],
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

                removeItem(index) {
                    if (this.items.length > 0) {
                        this.items.splice(index, 1);
                    }
                },

                addItem() {
                    this.items.push({
                        producto_id: '',
                        codigo: '',
                        nombre: '',
                        cantidad: '',
                        cantidad_millar: '',
                        unidad_medida: '',
                        fardo: '',
                        total_kilos: '',
                        total_millares: '',
                        precio_unitario: '',
                        precio_total: '',
                        unidad: '',
                        estado_item: 'Activo',
                        motivo_rechazo: '',
                        precio_competencia: ''
                    });
                },

                updateProductData(index, event) {
                    let productoSeleccionadoId = event.target.value;

                    // Validación de duplicados
                    if (productoSeleccionadoId !== "") {
                        let existeDuplicado = this.items.some((item, i) => item.producto_id == productoSeleccionadoId && i !== index);

                        if (existeDuplicado) {
                            alert('Este producto ya ha sido agregado al pedido. Por favor, actualice la cantidad en la fila correspondiente.');

                            this.items[index].producto_id = '';
                            this.items[index].codigo = '';
                            this.items[index].unidad_medida = '';
                            this.items[index].precio_unitario = 0;
                            const nombrePlantilla = "{{ $plantilla->nombre }}";
                            if (nombrePlantilla === 'Universal') this.items[index].unidad = '';

                            $(event.target).val('').trigger('change');

                            return;
                        }
                    }

                    const sel = event.target;
                    const opt = sel.options[sel.selectedIndex];
                    const nombrePlantilla = "{{ $plantilla->nombre }}";

                    if (opt.value === "") {
                        this.items[index].codigo = '';
                        this.items[index].nombre = '';
                        this.items[index].precio_unitario = 0;
                        if (nombrePlantilla === 'Universal') this.items[index].unidad = '';
                    } else {
                        this.items[index].producto_id = productoSeleccionadoId;
                        this.items[index].codigo = opt.getAttribute('data-codigo');
                        this.items[index].nombre = opt.text.trim();
                        this.items[index].precio_unitario = parseFloat(opt.getAttribute('data-precio') || 0);
                        this.items[index].unidad_medida = opt.getAttribute('data-unidad') || '-';

                        if (nombrePlantilla === 'Universal') {
                            this.items[index].unidad = opt.getAttribute('data-unidad') || '';
                        }
                    }
                    this.calculateRow(index);
                },

                calculateRow(index) {
                    const i = this.items[index];
                    const nombrePlantilla = "{{ $plantilla->nombre }}";

                    if (nombrePlantilla === 'Universal') {
                        i.precio_total = (parseFloat(i.cantidad) || 0) * (parseFloat(i.precio_unitario) || 0);
                    } else if (nombrePlantilla === 'Bolsas de Polipropileno') {
                        i.total_kilos = (parseFloat(i.cantidad) || 0) * (parseFloat(i.fardo) || 0);
                        i.precio_total = i.total_kilos * (parseFloat(i.precio_unitario) || 0);
                    } else if (nombrePlantilla === 'Bolsas de Polipropileno por kilos') {
                        i.precio_total = (parseFloat(i.total_kilos) || 0) * (parseFloat(i.precio_unitario) || 0);
                    } else {
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
                        
                        if (client.contacto) {
                            this.contacto.nombre = client.contacto.nombre;
                        }
                    } else {
                        this.cliente = { nombre: '', ruc: '', direccion: '', condicion_pago: '', provincia: '' };
                        this.contacto = { nombre: '' };
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
                        subtotal: symbol + st.toLocaleString('en-US', { minimumFractionDigits: 2 }),
                        igv: symbol + igv.toLocaleString('en-US', { minimumFractionDigits: 2 }),
                        total: symbol + tot.toLocaleString('en-US', { minimumFractionDigits: 2 }),
                        totalInSoles: parseFloat(totSoles).toLocaleString('en-US', { minimumFractionDigits: 2 })
                    }
                },

                submitForm() {
                    if (!this.cliente_id) return alert('Por favor, seleccione un cliente antes de continuar.');
                    if (this.items.some(i => !i.producto_id)) return alert('Hay filas sin producto seleccionado.');
                    if (confirm('¿Desea guardar este pedido directo?')) document.getElementById('form-pedido').submit();
                },

                abrirModalRechazo(index) {
                    this.rechazoIndex = index;
                    this.rechazoItemData = { motivo_rechazo: '', precio_competencia: '' };
                    this.modalRechazoOpen = true;
                },
                cerrarModalRechazo() {
                    this.modalRechazoOpen = false;
                    this.rechazoIndex = null;
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
                        let item = this.items[this.perdidaIndex];
                        item.estado_item = 'Rechazado';
                        item.oculto = true;
                        item.motivo_rechazo = this.perdidaData.proveedor_nombre;
                        item.perdida_data = JSON.parse(JSON.stringify(this.perdidaData));
                    }
                    this.cerrarModalPerdida();
                },

                consultarStock(index) {
                    const item = this.items[index];
                    if (!item.producto_id) return;

                    this.isStockLoading = true;
                    this.modalStockProduct.codigo = item.codigo || '';
                    this.modalStockProduct.nombre = item.nombre || 'Producto';
                    this.modalStockProduct.stock = '0.000';
                    this.modalStockProduct.ventas_hoy = [];
                    this.openStockModal = true;

                    fetch(`/api/productos/${item.producto_id}/monitoreo-stock`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error al consultar stock');
                        return res.json();
                    })
                    .then(data => {
                        this.modalStockProduct.stock = parseFloat(data.stock || 0).toFixed(3);
                        this.modalStockProduct.ventas_hoy = data.ventas_hoy || [];
                        this.isStockLoading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error al obtener datos del stock y ventas del día.');
                        this.isStockLoading = false;
                        this.openStockModal = false;
                    });
                },
                cerrarStockModal() {
                    this.openStockModal = false;
                    this.modalStockProduct = {
                        id: null,
                        codigo: '',
                        nombre: '',
                        stock: '0.000',
                        ventas_hoy: []
                    };
                    this.isStockLoading = false;
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
