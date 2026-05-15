<div class="bg-gray-100 p-4 border border-t-0 rounded-b-lg mb-6 grid grid-cols-2 gap-4 text-sm">
    <div class="col-span-2 text-lg font-bold text-[#333333] border-b pb-2 mb-2">
        <span class="mr-2">👤</span> CLIENTE: <span class="uppercase" x-text="cliente.nombre || 'SELECCIONE CLIENTE'"></span>
    </div>
    <div class="grid grid-cols-2 gap-4 text-sm mt-4">
    <div>
        <p><strong>RUC:</strong> <span x-text="cliente.ruc"></span></p>
        <p><strong>DIRECCIÓN:</strong> <span x-text="cliente.direccion"></span></p>
        <p><strong>CONDICIÓN PAGO:</strong> <span x-text="condicion_pago_cotizacion || cliente.condicion_pago || 'CONTADO'"></span></p>
    </div>
    <div>
        <p><strong>CONTACTO:</strong> <span x-text="contacto.nombre || 'N/A'"></span></p>
        <p><strong>MONEDA:</strong> <span x-text="moneda"></span></p>
        <p><strong>ATENDIDO POR:</strong> {{ auth()->user()->name }}</p>
    </div>
</div>
</div>