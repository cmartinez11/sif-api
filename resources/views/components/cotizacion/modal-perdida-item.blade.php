<div x-show="modalPerdidaOpen" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black bg-opacity-50 transition-opacity"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all"
         @click.away="cerrarModalPerdida()">
        
        <!-- Header -->
        <div class="bg-red-600 px-6 py-4 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                Registro de Pérdida de Ítem
            </h3>
            <button @click="cerrarModalPerdida()" class="text-white hover:text-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Body and Footer protected by existence check -->
        <template x-if="typeof perdidaData !== 'undefined'">
            <div>
                <!-- Body -->
                <div class="p-6 space-y-4">
            <p class="text-sm text-gray-600 border-b pb-2 italic">

                Indique por qué perdimos este producto para mejorar nuestra competitividad.
            </p>

            <!-- Nombre de Competencia -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre de Competencia (¿Quién ganó?):</label>
                <input type="text" id="proveedor_nombre" x-model="perdidaData.proveedor_nombre" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"
                       placeholder="Nombre de la empresa o proveedor">
            </div>

            <!-- Motivo de Selección -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Motivo de Selección del Cliente:</label>
                <select id="motivo_perdida" x-model="perdidaData.motivo_perdida" 
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm">
                    <option value="">-- Seleccionar Motivo --</option>
                    <option value="Precio">Precio</option>
                    <option value="Tiempo de Entrega">Tiempo de Entrega</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <!-- Lógica Condicional: Precio -->
            <div x-show="perdidaData.motivo_perdida === 'Precio'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Precio de Competencia:</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-400">$</span>
                    <input type="number" id="precio_ofrecido" step="0.0001" x-model="perdidaData.precio_ofrecido" 
                           class="w-full pl-7 border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"
                           placeholder="0.0000">
                </div>
            </div>

            <!-- Lógica Condicional: Tiempo de Entrega -->
            <div x-show="perdidaData.motivo_perdida === 'Tiempo de Entrega'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 class="bg-gray-50 p-3 rounded-lg border border-gray-200 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 uppercase mb-1">Entrega Proveedor:</label>
                    <input type="text" id="entrega_proveedor" x-model="perdidaData.entrega_proveedor" 
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"
                           placeholder="Ej: 3 días">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 uppercase mb-1">Nuestra Entrega:</label>
                    <input type="text" id="nuestra_entrega" x-model="perdidaData.entrega_nuestra" 
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"
                           placeholder="Ej: 7 días">
                </div>
            </div>

            <!-- Lógica Condicional: Otros -->
            <div x-show="perdidaData.motivo_perdida === 'Otros'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Explicación Detallada:</label>
                <textarea id="detalle_perdida" x-model="perdidaData.detalle_perdida" rows="3" 
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"
                          placeholder="Describa el motivo..."></textarea>
            </div>
        </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
                    <button @click="removeItem(perdidaIndex); cerrarModalPerdida()" 
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-bold rounded-lg shadow transition">
                        Eliminar por Error
                    </button>
                    <div class="flex gap-3">
                        <button @click="cerrarModalPerdida()" 
                                class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">
                            Cancelar
                        </button>
                        <button @click="confirmarPerdida()" 
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow-lg transition transform hover:scale-105">
                            Confirmar Pérdida
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

