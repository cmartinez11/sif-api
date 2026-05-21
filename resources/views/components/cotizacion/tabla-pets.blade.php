<table class="w-full text-sm text-left">
    <thead class="bg-[#0CC954] text-white">
        <tr>
            <th class="px-2 py-2 w-10 text-center">ÍTEM</th>
            <th class="px-2 py-2 text-center">CÓDIGO</th>
            <th class="px-2 py-2 w-1/3 text-center">PRODUCTO</th>
            <th class="px-2 py-2 text-center">CANT. x MILLAR</th>
            <th class="px-2 py-2 text-center">CANT. SACO/CAJAS/ BOLSAS/ JUMBO</th>
            <th class="px-2 py-2 text-center">TOT. MILLARES</th>
            <th class="px-2 py-2 text-center">P. UNITARIO</th>
            <th class="px-2 py-2 text-center">TOTAL</th>
            <th class="px-2 py-2 text-center w-24 whitespace-nowrap">ACCIONES</th>
        </tr>
    </thead>
    <tbody>
        <template x-for="(item, index) in items" :key="index">
            <tr x-show="!item.oculto" class="border-b" :class="item.estado_item === 'Rechazado' ? 'bg-orange-50 opacity-75' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')">
                <td class="px-2 py-2 text-center" x-text="index + 1"></td>
                <td class="px-2 py-2">
                    <div class="flex items-center gap-1">
                        <input type="text" readonly x-model="item.codigo" class="w-full text-xs border-0 bg-transparent p-0 focus:ring-0">
                        <button type="button" x-show="item.producto_id" @click="consultarStock(index)" class="p-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition shrink-0" title="Ver Stock y Ventas">👁️</button>
                    </div>
                </td>
                {{-- Producto --}}
                <td class="px-2 py-2">
                    <select x-model="item.producto_id" 
                            x-init="
                                $nextTick(() => {
                                    $( $el ).select2({ placeholder: 'Seleccione...' })
                                    .on('change', (e) => {
                                        item.producto_id = e.target.value;
                                        updateProductData(index, e);
                                    })
                                    // --- NUEVO CÓDIGO: Fuerza el Focus al abrir ---
                                    .on('select2:open', () => {
                                        setTimeout(() => {
                                            const searchField = document.querySelector('.select2-container--open .select2-search__field');
                                            if (searchField) searchField.focus();
                                        }, 100);
                                    });
                                    // ----------------------------------------------
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
                </td>
                
                <td class="px-2 py-2">
                    <input type="number" x-model.number="item.cantidad_millar" @input="calculateRow(index)" class="w-full text-right text-xs border-gray-300 rounded">
                </td>
                <td class="px-2 py-2">
                    <input type="number" x-model.number="item.fardo" @input="calculateRow(index)" class="w-full text-right text-xs border-gray-300 rounded">
                </td>
                <td class="px-2 py-2 text-right font-medium">
                    <span x-text="Number(item.total_millares || 0).toFixed(2)"></span>
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

