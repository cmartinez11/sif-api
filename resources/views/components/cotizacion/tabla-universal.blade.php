<div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="bg-[#0CC954] text-white">
            <tr class="whitespace-nowrap">
                <th class="px-2 py-3 w-10 text-center">ÍTEM</th>
                <th class="px-2 py-3">CÓDIGO</th>
                <th class="px-2 py-3 min-w-[250px] w-1/3">PRODUCTO</th>
                <th class="px-2 py-3 text-right">CANTIDAD</th>
                <th class="px-2 py-3 text-center">U/M</th>
                <th class="px-2 py-3 text-right">P. UNITARIO</th>
                <th class="px-2 py-3 text-right">TOTAL</th>
                <th class="px-2 py-3 text-center w-24 whitespace-nowrap">ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(item, index) in items" :key="index">
                <tr x-show="!item.oculto" class="border-b" :class="item.estado_item === 'Rechazado' ? 'bg-orange-50 opacity-75' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')">
                    <td class="px-2 py-4 text-center" x-text="index + 1"></td>
                    
                    {{-- Código --}}
                    <td class="px-2 py-4">
                        <input type="text" readonly x-model="item.codigo" class="w-full text-xs border-0 bg-transparent min-w-[80px]">
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
                                                        document.querySelector('.select2-search__field').focus();
                                                    }, 50);
                                                });
                                            });
                                        "
                                        @change="updateProductData(index, $event)" 
                                        class="w-full text-xs border-gray-300 rounded">
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

                    {{-- Cantidad --}}
                    <td class="px-2 py-4">
                        <input type="number" x-model.number="item.cantidad" @input="calculateRow(index)" class="w-full text-right text-xs border-gray-300 rounded min-w-[80px] py-2">
                    </td>

                    {{-- Unidad de Medida (Automática) --}}
                    <td class="px-2 py-4 text-center text-xs text-gray-600 whitespace-nowrap" x-text="item.unidad || '-'"></td>

                    {{-- Precio Unitario --}}
                    <td class="px-2 py-4">
                        <input type="number" step="0.00001" x-model.number="item.precio_unitario" @input="calculateRow(index)" class="w-full text-right text-xs border-gray-300 rounded min-w-[100px] py-2">
                    </td>

                    {{-- Total Fila --}}
                    <td class="px-2 py-4 text-right font-bold whitespace-nowrap" x-text="Number(item.precio_total || 0).toFixed(2)"></td>
                    
                    
                    <td class="px-2 py-4 text-center text-red-500 flex items-center justify-center gap-2 min-w-[80px]">
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
