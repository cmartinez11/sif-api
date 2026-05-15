<!-- Modal de Rechazo (Alpine CSS) -->
<div x-show="modalRechazoOpen"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black bg-opacity-50 transition-opacity"
    @keydown.escape.window="cerrarModalRechazo()">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 animate-fade-in-down" @click.away="cerrarModalRechazo()">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-md font-bold text-orange-500">Rechazar Producto</h3>
            <button type="button" @click="cerrarModalRechazo()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div>
            <p class="text-sm text-gray-600 mb-4">Por favor indique el motivo por el cual el cliente rechazó este producto.</p>
            
            <div class="text-sm">
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Motivo del Rechazo *</label>
                    <textarea x-model="rechazoItemData.motivo_rechazo" rows="3" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50" placeholder="Ej: Precio muy alto, tiempo de entrega, etc." required></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Precio Competencia (Opcional)</label>
                    <input type="number" step="0.0001" x-model="rechazoItemData.precio_competencia" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50" placeholder="Ej: 0.25">
                    <p class="text-[10px] text-gray-500 mt-1">Si conoce el precio por el cual se perdió, por favor indíquelo.</p>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 pt-3 border-t mt-4">
            <button type="button" @click="cerrarModalRechazo()" class="px-4 py-2 text-sm bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium transition">Cancelar</button>
            <button type="button" @click="confirmarRechazo()" class="px-4 py-2 text-sm bg-orange-500 text-white rounded-md hover:bg-orange-600 font-medium transition shadow-md">Confirmar Rechazo</button>
        </div>
    </div>
</div>
