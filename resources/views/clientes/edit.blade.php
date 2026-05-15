<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Editar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-gray-50 min-h-screen px-2">
        <div class="max-w-full lg:max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RUC</label>
                            <input type="text" name="ruc" value="{{ old('ruc', $cliente->ruc) }}" required maxlength="11" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'readonly' : '' }}>
                            @error('ruc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre o Razón Social</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'readonly' : '' }}>
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 py-2 md:py-1.5 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'readonly' : '' }}>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Departamento</label>
                            <input type="text" name="departamento" value="{{ old('departamento', $cliente->departamento) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'readonly' : '' }}>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provincia</label>
                            <input type="text" name="provincia" value="{{ old('provincia', $cliente->provincia) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'readonly' : '' }}>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Distrito</label>
                            <input type="text" name="distrito" value="{{ old('distrito', $cliente->distrito) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'readonly' : '' }}>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Condición de Pago</label>
                            <select name="condicion_pago" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 pointer-events-none' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'tabindex="-1"' : '' }}>
                                <option value="CONTADO" {{ old('condicion_pago', $cliente->condicion_pago) === 'CONTADO' ? 'selected' : '' }}>CONTADO</option>
                                <option value="7 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '7 DIAS' ? 'selected' : '' }}>7 DIAS</option>
                                <option value="10 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '10 DIAS' ? 'selected' : '' }}>10 DIAS</option>
                                <option value="15 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '15 DIAS' ? 'selected' : '' }}>15 DIAS</option>
                                <option value="20 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '20 DIAS' ? 'selected' : '' }}>20 DIAS</option>
                                <option value="30 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '30 DIAS' ? 'selected' : '' }}>30 DIAS</option>
                                <option value="45 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '45 DIAS' ? 'selected' : '' }}>45 DIAS</option>
                                <option value="60 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '60 DIAS' ? 'selected' : '' }}>60 DIAS</option>
                                <option value="90 DIAS" {{ old('condicion_pago', $cliente->condicion_pago) === '90 DIAS' ? 'selected' : '' }}>90 DIAS</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Asociar a Contacto (Opcional)</label>
                            <select name="contacto_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50 py-2 md:py-1.5 {{ auth()->user()->hasRole('Vendedor') ? 'bg-gray-100 pointer-events-none' : '' }}" {{ auth()->user()->hasRole('Vendedor') ? 'tabindex="-1"' : '' }}>
                                <option value="">- Seleccionar Contacto -</option>
                                @foreach($contactos as $con)
                                    <option value="{{ $con->id }}" {{ old('contacto_id', $cliente->contacto_id) == $con->id ? 'selected' : '' }}>
                                        {{ $con->nombre }} ({{ $con->correo ?? 'Sin correo' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Perfil Técnico CRM: Selectores Dependientes (Hierárquicos) -->
                    <div class="mt-4 border border-gray-200 rounded-lg shadow-sm overflow-hidden bg-white" 
                         x-data="{ 
                            pico: '{{ old('tipo_preforma', $cliente->perfil->tipo_preforma ?? '') }}',
                            gramaje: '{{ old('gramaje', $cliente->perfil->gramaje ?? '') }}',
                            soplado: '{{ old('aplicacion', $cliente->perfil->aplicacion ?? '') }}',
                            tabla: {
                                'PICO 1881': {
                                    '12.4 GR': '300 ML - 330 ML',
                                    '15 GR': '500 ML',
                                    '17 GR': '500 ML - 600 ML',
                                    '20 GR': '625 ML',
                                    '30 GR': '1 LT - 1.5 LT',
                                    '57 GR': '3 LT - 5 LT'
                                },
                                'PICO 38 MM': {
                                    '30 GR': '1 LT',
                                    '45 GR': '2 LT',
                                    '60 GR': '3 LT'
                                },
                                'PICO 48 MM': {
                                    '60 GR': '3 LT',
                                    '90 GR': '5 LT',
                                    '100 GR': '7 LT',
                                    '106 GR': '10 LT'
                                },
                                'PICO 2621': {
                                    '9 GR': 'ACEITE 125 ML',
                                    '15 GR': 'ACEITE 250-500 ML',
                                    '23 GR': 'ACEITE 1 LT'
                                }
                            },
                            get gramajesDisponibles() {
                                return this.pico && this.tabla[this.pico] ? Object.keys(this.tabla[this.pico]) : [];
                            },
                            updateSoplado() {
                                if (this.pico && this.gramaje) {
                                    this.soplado = this.tabla[this.pico][this.gramaje] || '';
                                }
                            },
                            reset() {
                                this.gramaje = '';
                                this.soplado = '';
                            }
                         }">
                        <div class="bg-gray-100 px-3 py-1 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-[11px] font-bold text-fenix-green uppercase tracking-wider">Configurador Técnico Jerárquico</h3>
                            <span class="text-[9px] text-gray-400 font-medium">GUÍA DE PRODUCCIÓN</span>
                        </div>
                        
                        <div class="p-3 space-y-4">
                            <!-- FILA 1: Selectores Jerárquicos (Responsivo: Stack en móvil) -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-gray-50 p-2 rounded border border-gray-200 shadow-inner">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">1. Pico (Tipo)</label>
                                    <select name="tipo_preforma" x-model="pico" @change="reset()" class="w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-2 md:py-1 h-10 md:h-8 font-bold text-fenix-green">
                                        <option value="">- Seleccionar Pico -</option>
                                        <template x-for="p in Object.keys(tabla)" :key="p">
                                            <option :value="p" x-text="p"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">2. Gramaje</label>
                                    <select name="gramaje" x-model="gramaje" @change="updateSoplado()" :disabled="!pico" class="w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-2 md:py-1 h-10 md:h-8 disabled:bg-gray-200 disabled:cursor-not-allowed">
                                        <option value="">- Peso -</option>
                                        <template x-for="g in gramajesDisponibles" :key="g">
                                            <option :value="g" x-text="g"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">3. Soplado</label>
                                    <input type="text" name="aplicacion" x-model="soplado" readonly class="w-full rounded border-gray-300 bg-gray-100 shadow-sm text-xs py-2 md:py-1 h-10 md:h-8 font-semibold text-gray-600 cursor-not-allowed">
                                </div>
                            </div>

                            <!-- FILA 2: Inteligencia de Competencia (Stack en móvil) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Proveedor Actual</label>
                                    <input type="text" name="proveedor_actual" value="{{ old('proveedor_actual', $cliente->perfil->proveedor_actual ?? '') }}" placeholder="Nombre del competidor" class="block w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-2 md:py-1 h-10 md:h-8">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Tipo de Problema:</label>
                                    <select name="problemas_proveedor" class="block w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-2 md:py-1 h-10 md:h-8">
                                        <option value="">- Seleccionar Problema -</option>
                                        <option value="Precio" {{ old('problemas_proveedor', $cliente->perfil->problemas_proveedor ?? '') === 'Precio' ? 'selected' : '' }}>Precio</option>
                                        <option value="Calidad" {{ old('problemas_proveedor', $cliente->perfil->problemas_proveedor ?? '') === 'Calidad' ? 'selected' : '' }}>Calidad</option>
                                        <option value="Entrega" {{ old('problemas_proveedor', $cliente->perfil->problemas_proveedor ?? '') === 'Entrega' ? 'selected' : '' }}>Entrega</option>
                                        <option value="Stock" {{ old('problemas_proveedor', $cliente->perfil->problemas_proveedor ?? '') === 'Stock' ? 'selected' : '' }}>Stock</option>
                                    </select>
                                </div>
                            </div>

                            <!-- FILA 3: Logística (Stack en móvil) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Cant. Máquinas</label>
                                    <input type="number" name="cant_maquinas" value="{{ old('cant_maquinas', $cliente->perfil->cant_maquinas ?? 0) }}" class="block w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-2 md:py-1 h-10 md:h-8">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Vol. Mensual</label>
                                    <input type="number" step="0.01" name="vol_mensual" value="{{ old('vol_mensual', $cliente->perfil->vol_mensual ?? 0) }}" class="block w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-2 md:py-1 h-10 md:h-8">
                                </div>
                                <div class="sm:col-span-2 md:col-span-2">
                                    <div class="flex flex-col sm:flex-row items-center gap-2">
                                        <div class="w-full sm:flex-1">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Frecuencia Compra</label>
                                            <select name="frecuencia_compra" class="block w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-2 md:py-1 h-10 md:h-8">
                                                <option value="">- Seleccionar -</option>
                                                <option value="Semanal" {{ old('frecuencia_compra', $cliente->perfil->frecuencia_compra ?? '') === 'Semanal' ? 'selected' : '' }}>Semanal</option>
                                                <option value="Quincenal" {{ old('frecuencia_compra', $cliente->perfil->frecuencia_compra ?? '') === 'Quincenal' ? 'selected' : '' }}>Quincenal</option>
                                                <option value="Mensual" {{ old('frecuencia_compra', $cliente->perfil->frecuencia_compra ?? '') === 'Mensual' ? 'selected' : '' }}>Mensual</option>
                                                <option value="Trimestral" {{ old('frecuencia_compra', $cliente->perfil->frecuencia_compra ?? '') === 'Trimestral' ? 'selected' : '' }}>Trimestral</option>
                                            </select>
                                        </div>
                                        <div class="w-full sm:w-auto flex flex-col">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Urgencia</label>
                                            <div class="flex items-center justify-center bg-white border border-gray-300 px-4 h-10 md:h-8 rounded shadow-sm">
                                                <input type="hidden" name="urgencias_frecuentes" value="0">
                                                <input type="checkbox" name="urgencias_frecuentes" id="urgencias_frecuentes" value="1" {{ old('urgencias_frecuentes', $cliente->perfil->urgencias_frecuentes ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-fenix-green h-5 w-5 md:h-4 md:w-4">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div>
                                <textarea name="observaciones" rows="2" placeholder="Observaciones estratégicas sobre el perfil técnico..." class="block w-full rounded border-gray-300 shadow-sm focus:border-fenix-green focus:ring-fenix-green text-xs py-1">{{ old('observaciones', $cliente->perfil->observaciones ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col md:flex-row justify-end gap-3">
                        <a href="{{ route('clientes.index') }}" class="w-full md:w-auto text-center text-gray-600 hover:text-gray-900 py-3 md:py-2 border border-gray-300 md:border-none rounded-md">Cancelar</a>
                        <button type="submit" class="w-full md:w-auto bg-fenix-gold hover:bg-yellow-500 text-gray-900 font-bold py-3 md:py-2 px-6 rounded shadow shadow-lg transition-all">Actualizar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>