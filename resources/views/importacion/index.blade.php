<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">{{ __('Importación Masiva') }}</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
                <div class="px-8 py-8 bg-gradient-to-r from-fenix-green to-green-600 text-white">
                    <h3 class="text-2xl font-bold">Importación Masiva de Clientes, Productos y Contactos</h3>
                    <p class="text-sm text-white/80 mt-2">Módulo exclusivo para administradores. Carga archivos Excel o CSV con los campos específicos de Plásticos Fénix para actualizar la base de datos de forma rápida y segura.</p>
                </div>

                <div class="p-8" x-data="{ uploading: false }">
                    @if(session('success'))
                        <div class="mb-6 rounded-xl border border-fenix-green/30 bg-fenix-green/5 p-5 text-fenix-green shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">✅</span>
                                <div>
                                    <p class="font-bold text-lg">Importación Exitosa</p>
                                    <p class="text-sm mt-1">{{ session('success') }}</p>
                                    @if(session('failures') && count(session('failures')))
                                        <div class="mt-4 rounded-lg bg-white/50 p-3 text-xs text-gray-700 border border-fenix-green/20">
                                            <p class="font-semibold text-red-600 mb-2">⚠️ Registros con errores (no procesados):</p>
                                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                                @foreach(session('failures') as $failure)
                                                    <li>{{ $failure }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-5 text-red-900 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">❌</span>
                                <div>
                                    <p class="font-bold text-lg">Errores en la Importación</p>
                                    <ul class="mt-3 list-disc list-inside text-sm space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-8 lg:grid-cols-3">
                        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800">Clientes</h4>
                                    <p class="text-sm text-gray-500">Importa clientes de Plásticos Fénix. (<span class="text-red-600 font-semibold">*</span> = Obligatorio)</p>
                                    <div class="mt-2 text-xs text-gray-600 space-y-1 font-mono">
                                        <p><span class="text-red-600">*</span> ruc <span class="text-gray-400">| Identificador único</span></p>
                                        <p><span class="text-red-600">*</span> razon_social <span class="text-gray-400">| Nombre empresa</span></p>
                                        <p><span class="text-red-600">*</span> condicion_pago <span class="text-gray-400">| CONTADO, 7 DIAS, etc.</span></p>
                                        <p>direccion <span class="text-gray-400">| Opcional</span></p>
                                        <p>departamento <span class="text-gray-400">| Opcional</span></p>
                                        <p>provincia <span class="text-gray-400">| Opcional</span></p>
                                        <p>distrito <span class="text-gray-400">| Opcional</span></p>
                                        <p>nombre_contacto <span class="text-gray-400">| Opcional</span></p>
                                        <p>enlace_contacto <span class="text-gray-400">| Opcional (URL)</span></p>
                                    </div>
                                </div>
                                <a href="{{ route('importacion.template', 'clientes') }}" class="inline-flex items-center gap-2 rounded-full bg-fenix-green px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">
                                    Descargar plantilla
                                </a>
                            </div>

                            <form action="{{ route('importacion.clientes') }}" method="POST" enctype="multipart/form-data" @submit="uploading = true">
                                @csrf
                                <label class="block text-sm font-semibold text-gray-700">Selecciona archivo</label>
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-fenix-green focus:outline-none focus:ring-2 focus:ring-fenix-green/20">
                                <button type="submit" class="mt-4 w-full rounded-2xl bg-fenix-green px-4 py-3 text-sm font-bold text-white shadow hover:bg-green-700 transition">Importar Clientes</button>
                            </form>
                        </section>

                        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800">Productos</h4>
                                    <p class="text-sm text-gray-500">Importa productos de catálogo. (<span class="text-red-600 font-semibold">*</span> = Obligatorio)</p>
                                    <div class="mt-2 text-xs text-gray-600 space-y-1 font-mono">
                                        <p><span class="text-red-600">*</span> codigo <span class="text-gray-400">| Identificador único</span></p>
                                        <p><span class="text-red-600">*</span> nombre_producto <span class="text-gray-400">| Nombre</span></p>
                                        <p><span class="text-red-600">*</span> precio_base <span class="text-gray-400">| Puede ser 0</span></p>
                                        <p><span class="text-red-600">*</span> linea <span class="text-gray-400">| BOBINA AD, PET, etc.</span></p>
                                        <p>sublinea <span class="text-gray-400">| Opcional</span></p>
                                        <p>estado <span class="text-gray-400">| Opcional (1/Activo, 0/Inactivo)</span></p>
                                        <p>peso <span class="text-gray-400">| Opcional (kg)</span></p>
                                        <p>stock <span class="text-gray-400">| Opcional (Defecto 0)</span></p>
                                        <p>unidad_medida <span class="text-gray-400">| Opcional</span></p>
                                        <p>unidad_medida_logistica <span class="text-gray-400">| Opcional</span></p>
                                    </div>
                                </div>
                                <a href="{{ route('importacion.template', 'productos') }}" class="inline-flex items-center gap-2 rounded-full bg-fenix-green px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">
                                    Descargar plantilla
                                </a>
                            </div>

                            <form action="{{ route('importacion.productos') }}" method="POST" enctype="multipart/form-data" @submit="uploading = true">
                                @csrf
                                <label class="block text-sm font-semibold text-gray-700">Selecciona archivo</label>
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-fenix-green focus:outline-none focus:ring-2 focus:ring-fenix-green/20">
                                <button type="submit" class="mt-4 w-full rounded-2xl bg-fenix-green px-4 py-3 text-sm font-bold text-white shadow hover:bg-green-700 transition">Importar Productos</button>
                            </form>
                        </section>

                        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800">Contactos</h4>
                                    <p class="text-sm text-gray-500">Importa contactos comerciales. (<span class="text-red-600 font-semibold">*</span> = Obligatorio)</p>
                                    <div class="mt-2 text-xs text-gray-600 space-y-1 font-mono">
                                        <p><span class="text-red-600">*</span> nombre_completo <span class="text-gray-400">| Nombre</span></p>
                                        <p>telefono <span class="text-gray-400">| Opcional</span></p>
                                        <p>correo_electronico <span class="text-gray-400">| Opcional</span></p>
                                    </div>
                                </div>
                                <a href="{{ route('importacion.template', 'contactos') }}" class="inline-flex items-center gap-2 rounded-full bg-fenix-green px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">
                                    Descargar plantilla
                                </a>
                            </div>

                            <form action="{{ route('importacion.contactos') }}" method="POST" enctype="multipart/form-data" @submit="uploading = true">
                                @csrf
                                <label class="block text-sm font-semibold text-gray-700">Selecciona archivo</label>
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-2 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-fenix-green focus:outline-none focus:ring-2 focus:ring-fenix-green/20">
                                <button type="submit" class="mt-4 w-full rounded-2xl bg-fenix-green px-4 py-3 text-sm font-bold text-white shadow hover:bg-green-700 transition">Importar Contactos</button>
                            </form>
                        </section>
                    </div>

                    <div class="mt-8" x-show="uploading" x-cloak>
                        <div class="rounded-2xl border border-fenix-green bg-fenix-green/10 p-4 text-fenix-green">
                            <div class="mb-2 flex items-center gap-3 text-sm font-semibold">
                                <span class="inline-flex h-3 w-3 animate-pulse rounded-full bg-fenix-green"></span>
                                Procesando importación, por favor espere...
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-white shadow-sm">
                                <div class="h-full w-full animate-pulse rounded-full bg-fenix-green"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
