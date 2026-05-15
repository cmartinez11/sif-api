<!-- Modal de Venta Perdida (Alpine.js) -->
<div x-show="modalVentaPerdidaOpen" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black bg-opacity-50"
     x-cloak
     style="display: none;"
     @keydown.escape.window="modalVentaPerdidaOpen = false">
    
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-0 overflow-hidden animate-fade-in-down"
         @click.away="modalVentaPerdidaOpen = false">
        
        <div class="flex justify-between items-center p-4 bg-red-600 text-white">
            <h3 class="text-lg font-bold">Registrar Venta Perdida / Cancelación</h3>
            <button type="button" @click="modalVentaPerdidaOpen = false" class="text-white hover:text-red-200 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="p-6">
            <div x-data="{ motivo: '', proveedor: '', detalle: '', loading: false }">
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Motivo Principal <span class="text-red-500">*</span></label>
                    <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50" 
                            x-model="motivo" required>
                        <option value="">-- Seleccione un motivo --</option>
                        <option value="Precio alto">Precio alto</option>
                        <option value="Tiempo de entrega largo">Tiempo de entrega largo</option>
                        <option value="Falta de stock">Falta de stock</option>
                        <option value="Problemas de calidad">Problemas de calidad</option>
                        <option value="Relación con otro proveedor">Relación con otro proveedor</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Proveedor Ganador (Opcional)</label>
                    <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50" 
                           x-model="proveedor" placeholder="Nombre de la competencia">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Detalles u Observaciones</label>
                    <textarea class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50" 
                              x-model="detalle" rows="3" placeholder="Añade más información sobre la cancelación..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="modalVentaPerdidaOpen = false" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium transition">Cancelar</button>
                    <button type="button" 
                            @click="
                                if(!motivo) { alert('Seleccione un motivo'); return; }
                                loading = true;
                                let url = '{{ route('crm.cotizacion.perdida', ':id') }}'.replace(':id', selectedCotizacionId);
                                fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({
                                        motivo_perdida: motivo,
                                        proveedor_ganador: proveedor,
                                        detalle_perdida: detalle
                                    })
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if(data.success) {
                                        window.location.reload();
                                    } else {
                                        alert(data.message || 'Error desconocido');
                                        loading = false;
                                    }
                                })
                                .catch(err => {
                                    console.error(err);
                                    alert('Error al procesar la solicitud');
                                    loading = false;
                                })
                            "
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium transition shadow-md"
                            :disabled="loading">
                        <span x-text="loading ? 'Procesando...' : 'Confirmar Cancelación'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
