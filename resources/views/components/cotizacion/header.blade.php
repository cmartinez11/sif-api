<div class="flex justify-between items-start mb-6 border-b pb-4">
    <div class="flex items-center">
        <img src="/logo2.png" alt="Logo" style="width:180px; height: auto; object-fit: contain;" />
    </div>
    <div class="text-right">
        <h1 class="text-3xl font-bold text-[#0CC954] tracking-wider">COTIZACIÓN</h1>
        <p class="text-lg text-gray-700 font-semibold mt-2">N° - {{ $numero_cotizacion ?? '0000000000Borrador' }}</p>
    </div>
</div>
<div class="bg-[#0CC954] text-white p-3 rounded-t-lg flex justify-between">
    <div>
        <p class="font-bold">PLASTICOS FENIX EIRL</p>
        <p class="font-bold">RUC: 20522086704</p>
        <p class="text-sm">Jr. Loreto Posesionarios Mypes de Villa Sol. Jicamarca Lima - Huarochirí</p>
    </div>
    <div class="text-right flex flex-col justify-center">
        <p class="text-sm flex items-center justify-end"><span class="mr-2">📅</span> FECHA DE EMISIÓN</p>
        <p class="font-bold">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>
    </div>
</div>
