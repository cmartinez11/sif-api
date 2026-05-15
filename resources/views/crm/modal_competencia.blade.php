<!-- Modal de Competencia (Tailwind CSS + jQuery) -->
<div id="modalCompetencia" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black bg-opacity-50 hidden" style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 animate-fade-in-down">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-md font-bold text-[#0CC954]" id="modalCompetenciaTitle">Precio Competencia</h3>
            <button type="button" onclick="cerrarModalCompetencia()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div>
            <form id="formCompetencia" class="text-sm">
                @csrf
                <input type="hidden" id="comp_cliente_id" name="cliente_id" value="">
                <input type="hidden" id="comp_producto_id" name="producto_id" value="">

                <div class="mb-4">
                    <label for="comp_proveedor_nombre" class="block text-xs font-medium text-gray-700 mb-1">Proveedor (Nombre)</label>
                    <input type="text" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-[#0CC954] focus:ring focus:ring-[#0CC954] focus:ring-opacity-50" id="comp_proveedor_nombre" name="proveedor_nombre" required>
                </div>

                <div class="mb-4">
                    <label for="comp_precio_ofrecido" class="block text-xs font-medium text-gray-700 mb-1">Precio Ofrecido</label>
                    <input type="number" step="0.0001" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-[#0CC954] focus:ring focus:ring-[#0CC954] focus:ring-opacity-50" id="comp_precio_ofrecido" name="precio_ofrecido" required>
                </div>

                <div class="mb-4">
                    <label for="comp_unidad_volumen" class="block text-xs font-medium text-gray-700 mb-1">Unidad de Volumen</label>
                    <input type="text" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-[#0CC954] focus:ring focus:ring-[#0CC954] focus:ring-opacity-50" id="comp_unidad_volumen" name="unidad_volumen" placeholder="Ej: Millar, Saco, etc.">
                </div>

                <div class="mb-5">
                    <label for="comp_fecha_dato" class="block text-xs font-medium text-gray-700 mb-1">Fecha del Dato</label>
                    <input type="date" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-[#0CC954] focus:ring focus:ring-[#0CC954] focus:ring-opacity-50" id="comp_fecha_dato" name="fecha_dato" value="{{ date('Y-m-d') }}" required>
                </div>
            </form>
        </div>
        
        <div class="flex justify-end gap-3 pt-3 border-t">
            <button type="button" onclick="cerrarModalCompetencia()" class="px-4 py-2 text-sm bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 font-medium transition">Cancelar</button>
            <button type="button" id="btnGuardarCompetencia" onclick="guardarCompetencia()" class="px-4 py-2 text-sm bg-[#0CC954] text-white rounded-md hover:bg-green-700 font-medium transition shadow-md">Guardar</button>
        </div>
    </div>
</div>

<script>
    function cerrarModalCompetencia() {
        $('#modalCompetencia').fadeOut(200);
    }

    function abrirModalCompetencia(clienteId, productoId, productoNombre) {
        $('#formCompetencia')[0].reset();
        $('#comp_fecha_dato').val("{{ date('Y-m-d') }}");
        $('#comp_cliente_id').val(clienteId);
        $('#comp_producto_id').val(productoId);
        
        let title = productoNombre ? 'Precio Competencia: ' + productoNombre.trim() : 'Registrar Precio de Competencia';
        $('#modalCompetenciaTitle').text(title);
        
        // Mostrar como flexbox usando jQuery
        $('#modalCompetencia').fadeIn(200).css('display', 'flex');
    }

    function guardarCompetencia() {
        let data = {
            _token: $('input[name="_token"]').val(),
            cliente_id: $('#comp_cliente_id').val(),
            producto_id: $('#comp_producto_id').val(),
            proveedor_nombre: $('#comp_proveedor_nombre').val(),
            precio_ofrecido: $('#comp_precio_ofrecido').val(),
            unidad_volumen: $('#comp_unidad_volumen').val(),
            fecha_dato: $('#comp_fecha_dato').val()
        };

        let $btn = $('#btnGuardarCompetencia');
        $btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: "{{ route('crm.competencia.store') }}",
            type: "POST",
            data: data,
            success: function(response) {
                $btn.prop('disabled', false).text('Guardar');
                if(response.status === 'success') {
                    cerrarModalCompetencia();
                    $('#formCompetencia')[0].reset();
                    alert('Éxito: ' + response.message);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Guardar');
                let errorMsg = 'Ocurrió un error al guardar.';
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                alert('Error:\n' + errorMsg);
            }
        });
    }
</script>
