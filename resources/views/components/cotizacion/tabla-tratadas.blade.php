<div class="overflow-x-auto">
    <table class="w-full text-sm text-left border-collapse">
        <thead class="bg-[#0CC954] text-white">
            <tr class="whitespace-nowrap">
                <th class="px-2 py-3 w-10 text-center">ÍTEM</th>
                <th class="px-2 py-3 text-center">CÓDIGO</th>
                <th class="px-2 py-3 min-w-[250px] w-1/3 text-center">PRODUCTO</th>
                <th class="px-2 py-3 text-center">CANT. x MILLAR</th>
                <th class="px-2 py-3 text-center">FARDO</th>
                <th class="px-2 py-3 text-center">TOT. MILLARES</th>
                <th class="px-2 py-3 text-center">P. UNITARIO</th>
                <th class="px-2 py-3 text-center">TOTAL</th>
                <th class="px-2 py-3 text-center no-print w-24 whitespace-nowrap">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(item, index) in items" :key="index">
                <tr x-show="!item.oculto" class="border-b" :class="item.estado_item === 'Rechazado' ? 'bg-orange-50 opacity-75' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')">
                    <td class="px-2 py-4 text-center font-medium" x-text="index + 1"></td>
                    
                    <td class="px-2 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <span class="text-xs text-gray-500 whitespace-nowrap font-mono" x-text="item.codigo || '-'"></span>
                            <button type="button" x-show="item.producto_id" @click="consultarStock(index)" class="p-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition shrink-0" title="Ver Stock y Ventas">👁️</button>
                        </div>
                    </td>

                    {{-- Producto --}}
                    <td class="px-2 py-4">
                        <div class="min-w-[200px]">
                            <select x-model="item.producto_id" 
                                    x-init="
                                        $nextTick(() => {
                                            $( $el ).select2({ placeholder: 'Seleccione...' })
                                            .on('change', (e) => {
                                                item.producto_id = e.target.value;
                                                updateProductData(index, e);
                                            })
                                            .on('select2:open', () => {
                                                setTimeout(() => {
                                                    const searchField = document.querySelector('.select2-container--open .select2-search__field');
                                                    if (searchField) searchField.focus();
                                                }, 100);
                                            });
                                        });
                                    "
                                    @change="updateProductData(index, $event)" 
                                    class="w-full text-xs border-gray-300 rounded select-producto">
                                <option value="">Seleccione...</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}" 
                                            data-codigo="{{ $prod->codigo }}" 
                                            data-precio="{{ $prod->precio_base }}"
                                            data-unidad="{{ $prod->unidad_medida }}">
                                        {{ $prod->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>

                    <td class="px-2 py-4">
                        <input type="number" x-model.number="item.cantidad_millar" 
                               @input="calculateRow(index)" 
                               class="w-full text-right text-xs border-gray-300 rounded py-2 min-w-[80px]">
                    </td>

                    <td class="px-2 py-4">
                        <input type="number" x-model.number="item.fardo" 
                               @input="calculateRow(index)" 
                               class="w-full text-right text-xs border-gray-300 rounded py-2 min-w-[80px]">
                    </td>

                    <td class="px-2 py-4 text-right font-medium text-gray-700 whitespace-nowrap">
                        <span x-text="Number(item.total_millares || 0).toFixed(2)"></span>
                    </td>

                    <td class="px-2 py-4">
                        <input type="number" step="0.00001" x-model.number="item.precio_unitario" 
                               @input="calculateRow(index)" 
                               class="w-full text-right text-xs border-gray-300 rounded py-2 min-w-[100px]">
                    </td>

                    <td class="px-2 py-4 text-right font-bold text-green-800 whitespace-nowrap">
                        <span x-text="moneda === 'soles' ? 'S/ ' : '$ '"></span>
                        <span x-text="Number(item.precio_total || 0).toFixed(2)"></span>
                    </td>


                    <td class="px-2 py-4 text-center no-print flex items-center justify-center gap-2 min-w-[80px]">
                        <button type="button" x-show="item.estado_item === 'Rechazado'" @click="item.estado_item = 'Activo'; item.motivo_rechazo = ''; item.precio_competencia = ''" class="p-2 bg-green-50 text-green-500 rounded hover:bg-green-100 transition" title="Restaurar Ítem">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" @click="abrirModalPerdida(index)" class="p-2 bg-red-50 text-red-500 rounded hover:bg-red-100 transition" title="Eliminar fila">✖</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>
