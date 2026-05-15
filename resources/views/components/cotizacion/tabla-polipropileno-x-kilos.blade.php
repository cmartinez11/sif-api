<table class="w-full text-sm text-left">
    <thead class="bg-[#0CC954] text-white">
        <tr>
            <th class="px-2 py-2 w-10 text-center">ÍTEM</th>
            <th class="px-2 py-2 text-center">CÓDIGO</th>
            <th class="px-2 py-2 w-1/3 text-center">PRODUCTO</th>
            <th class="px-2 py-2 text-center">CANTIDAD DE FARDOS</th>
            <th class="px-2 py-2 text-center">U/M</th>
            <th class="px-2 py-2 text-center">TOTAL KILOS</th>
            <th class="px-2 py-2 text-center">PRECIO UNITARIO</th>
            <th class="px-2 py-2 text-center">TOTAL</th>
            <th class="px-2 py-2 text-center w-24 whitespace-nowrap">ACCIONES</th>
        </tr>
    </thead>
    <tbody>
        <template x-for="(item, index) in items" :key="index">
            <tr x-show="!item.oculto" class="border-b" :class="item.estado_item === 'Rechazado' ? 'bg-orange-50 opacity-75' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')">
                <td class="px-2 py-2 text-center" x-text="index + 1"></td>
                <td class="px-2 py-2">
                    <input type="text" readonly x-model="item.codigo" class="w-full text-xs border-0 bg-transparent">
                </td>
                {{-- Producto --}}
                <td class="px-2 py-2">
                    <select x-model="item.producto_id" 
                            x-init="
                                $nextTick(() => {
                                    $( $el ).select2({ placeholder: 'Seleccione...' })
                                    .on('change', (e) => {
                                        item.producto_id = e.target.value;
                                        item.unidad_medida = e.target.options[e.target.selectedIndex].dataset.unidad || '-';
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
                </td>
                <td class="px-2 py-2">
                    <input type="number" x-model.number="item.cantidad_fardos" class="w-full text-right text-xs border-gray-300 rounded">
                </td>
                <td class="px-2 py-2 text-center">
                    <span class="text-xs font-medium text-gray-600" x-text="item.unidad_medida || '-'"></span>
                </td>
                <td class="px-2 py-2">
                    <input type="number" step="0.01" x-model.number="item.total_kilos" @input="calculateRow(index)" class="w-full text-right text-xs border-gray-300 rounded">
                </td>
                <td class="px-2 py-2">
                    <input type="number" step="0.00001" x-model.number="item.precio_unitario" @input="calculateRow(index)" class="w-full text-right text-xs border-gray-300 rounded">
                </td>
                <td class="px-2 py-2 text-right font-bold" x-text="Number(item.precio_total || 0).toFixed(2)"></td>
                
                
                <td class="px-2 py-2 text-center text-red-500 flex items-center justify-center gap-1 min-w-[80px]">
                    <button type="button" x-show="item.estado_item === 'Rechazado'" @click="item.estado_item = 'Activo'; item.motivo_rechazo = ''; item.precio_competencia = ''" class="btn btn-xs text-green-500 hover:text-green-700 cursor-pointer" title="Restaurar Ítem">
                        <i class="fas fa-check"></i>
                    </button>
                    <button type="button" @click="abrirModalPerdida(index)" class="btn btn-xs btn-outline-danger text-red-500 hover:text-red-700 cursor-pointer" title="Eliminar fila">✖</button>
                </td>
            </tr>
        </template>
    </tbody>
</table>
