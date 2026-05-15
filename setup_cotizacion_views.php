<?php
$dirs = [
    __DIR__ . '/resources/views/cotizaciones',
    __DIR__ . '/resources/views/components/cotizacion',
    __DIR__ . '/resources/views/pdf'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// ---------------- COMPONENTS ----------------
$header = <<<'EOD'
<div class="flex justify-between items-start mb-6 border-b pb-4">
    <div class="flex items-center">
        <!-- Reemplazar con <img src="" /> -->
        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center text-xs font-bold text-gray-500">
            Grupo Fénix
        </div>
    </div>
    <div class="text-right">
        <h1 class="text-3xl font-bold text-[#1a472a] tracking-wider">COTIZACIÓN</h1>
        <p class="text-lg text-gray-700 font-semibold mt-2">N° - {{ $numero_cotizacion ?? '0000000000Borrador' }}</p>
    </div>
</div>
<div class="bg-[#1a472a] text-white p-3 rounded-t-lg flex justify-between">
    <div>
        <p class="font-bold">RUC: 20522086704</p>
        <p class="text-sm">Dirección empresa</p>
    </div>
    <div class="text-right flex flex-col justify-center">
        <p class="text-sm flex items-center justify-end"><span class="mr-2">📅</span> FECHA DE EMISIÓN</p>
        <p class="font-bold">{{ date('d \d\e F \d\e Y') }}</p>
    </div>
</div>
EOD;

$clienteInfo = <<<'EOD'
<div class="bg-gray-100 p-4 border border-t-0 rounded-b-lg mb-6 grid grid-cols-2 gap-4 text-sm">
    <div class="col-span-2 text-lg font-bold text-[#333333] border-b pb-2 mb-2">
        <span class="mr-2">👤</span> CLIENTE: <span class="uppercase">{{ $cliente->nombre ?? 'SELECCIONE CLIENTE' }}</span>
    </div>
    <div>
        <p><span class="font-bold">RUC:</span> {{ $cliente->ruc ?? '' }}</p>
        <p><span class="font-bold">DIRECCIÓN:</span> {{ $cliente->direccion ?? '' }}</p>
        <p><span class="font-bold">CONDICIÓN PAGO:</span> {{ $cliente->condicion_pago ?? 'CONTADO' }}</p>
    </div>
    <div>
        <p><span class="font-bold">AGENCIA / DIR:</span> {{ $agencia ?? 'N/A' }}</p>
        <p><span class="font-bold">PROVINCIA:</span> {{ $cliente->provincia ?? '' }}</p>
        <p><span class="font-bold">MONEDA:</span> <span x-text="moneda == 'soles' ? 'SOLES' : 'DOLARES'"></span></p>
        <p><span class="font-bold">ATENDIDO POR:</span> {{ auth()->user()->name }}</p>
    </div>
</div>
EOD;

$footerBanco = <<<'EOD'
<div class="mt-8 pt-4 border-t flex justify-between">
    <div class="w-1/2 pr-4 border-r">
        <h4 class="font-bold text-[#1a472a] mb-2">CUENTAS BANCARIAS</h4>
        <div class="text-sm text-gray-700">
            <p><strong>BCP SOLES:</strong> 191-00000000-0-00</p>
            <p><strong>BCP DÓLARES:</strong> 191-00000000-1-00</p>
        </div>
    </div>
    <div class="w-1/2 pl-4 flex flex-col items-center justify-center bg-gray-50 rounded p-4">
        <p class="font-bold text-center text-gray-500 mb-2">"TU MARCA SIEMPRE RELEVANTE"</p>
        <div class="text-xs text-gray-400 mt-4 flex justify-between w-full">
            <span>Síguenos: @plasticosfenix</span>
            <span>comercial@plasticosfenix.com</span>
        </div>
    </div>
</div>
EOD;

// Plantillas Componentes (Ejemplo de uno base con Alpine)
$tablaTratadas = <<<'EOD'
<table class="w-full text-sm text-left">
    <thead class="bg-[#1a472a] text-white">
        <tr>
            <th class="px-2 py-2 w-10 text-center">ÍTEM</th>
            <th class="px-2 py-2">CÓDIGO</th>
            <th class="px-2 py-2 w-1/3">PRODUCTO</th>
            <th class="px-2 py-2 text-right">CANT. x MILLAR</th>
            <th class="px-2 py-2 text-right">FARDO</th>
            <th class="px-2 py-2 text-right">TOT. MILLARES</th>
            <th class="px-2 py-2 text-right">P. UNITARIO</th>
            <th class="px-2 py-2 text-right">TOTAL</th>
            <th class="px-2 py-2 text-center">X</th>
        </tr>
    </thead>
    <tbody>
        <template x-for="(item, index) in items" :key="index">
            <tr class="border-b" :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                <td class="px-2 py-2 text-center" x-text="index + 1"></td>
                <td class="px-2 py-2">
                    <input type="text" readonly x-model="item.codigo" class="w-full text-xs border-0 bg-transparent">
                </td>
                <td class="px-2 py-2">
                    <select x-model="item.producto_id" @change="updateProductData(index)" class="w-full text-xs border-gray-300 rounded">
                        <option value="">Seleccione...</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}" data-codigo="{{ $prod->codigo }}" data-precio="{{ $prod->precio_base }}">{{ $prod->nombre }}</option>
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
                <td class="px-2 py-2 text-center text-red-500 cursor-pointer" @click="removeItem(index)">✖</td>
            </tr>
        </template>
    </tbody>
</table>
EOD;

file_put_contents(__DIR__ . '/resources/views/components/cotizacion/header.blade.php', $header);
file_put_contents(__DIR__ . '/resources/views/components/cotizacion/cliente-info.blade.php', $clienteInfo);
file_put_contents(__DIR__ . '/resources/views/components/cotizacion/footer-banco.blade.php', $footerBanco);
file_put_contents(__DIR__ . '/resources/views/components/cotizacion/tabla-tratadas.blade.php', $tablaTratadas);

echo "Base components for cotizacion generated.\n";
