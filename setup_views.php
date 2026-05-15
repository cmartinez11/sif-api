<?php
$dirs = [
    __DIR__ . '/resources/views/clientes',
    __DIR__ . '/resources/views/productos',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// ------ CLIENTES VIEWS ------
$clientesIndex = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Gestión de Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Listado de Clientes</h3>
                        <a href="{{ route('clientes.create') }}" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150">
                            + Añadir Cliente
                        </a>
                    </div>
                    
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-fenix-green">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">RUC</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Dirección</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($clientes as $cliente)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $cliente->ruc }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $cliente->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $cliente->direccion ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-3">
                                            <a href="{{ route('clientes.edit', $cliente) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay clientes registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$clientesCreate = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Crear Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RUC</label>
                            <input type="text" name="ruc" value="{{ old('ruc') }}" required maxlength="11" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('ruc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre o Razón Social</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provincia</label>
                            <input type="text" name="provincia" value="{{ old('provincia') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Condición de Pago</label>
                            <input type="text" name="condicion_pago" value="{{ old('condicion_pago', 'CONTADO') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('clientes.index') }}" class="mr-3 text-gray-600 hover:text-gray-900 py-2">Cancelar</a>
                        <button type="submit" class="bg-fenix-gold hover:bg-yellow-500 text- fenxi-green font-bold py-2 px-6 rounded shadow">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$clientesEdit = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Editar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RUC</label>
                            <input type="text" name="ruc" value="{{ old('ruc', $cliente->ruc) }}" required maxlength="11" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('ruc') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre o Razón Social</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Provincia</label>
                            <input type="text" name="provincia" value="{{ old('provincia', $cliente->provincia) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Condición de Pago</label>
                            <input type="text" name="condicion_pago" value="{{ old('condicion_pago', $cliente->condicion_pago) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('clientes.index') }}" class="mr-3 text-gray-600 hover:text-gray-900 py-2">Cancelar</a>
                        <button type="submit" class="bg-fenix-gold hover:bg-yellow-500 text-gray-900 font-bold py-2 px-6 rounded shadow">Actualizar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

// ------ PRODUCTOS VIEWS ------
$productosIndex = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Catálogo de Productos') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Inventario Maestro</h3>
                        <a href="{{ route('productos.create') }}" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150">
                            + Añadir Producto
                        </a>
                    </div>
                    
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-fenix-green">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Código</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre del Producto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">U/M</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Precio Base</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($productos as $producto)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $producto->codigo }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $producto->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $producto->unidad_medida }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($producto->precio_base, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-3">
                                            <a href="{{ route('productos.edit', $producto) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                            <form action="{{ route('productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay productos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$productosCreate = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Crear Producto') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('productos.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unidad de Medida (Ej: Millennium, Kilo, etc)</label>
                            <input type="text" name="unidad_medida" value="{{ old('unidad_medida') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio Base</label>
                            <input type="number" step="0.01" name="precio_base" value="{{ old('precio_base') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('productos.index') }}" class="mr-3 text-gray-600 hover:text-gray-900 py-2">Cancelar</a>
                        <button type="submit" class="bg-fenix-gold hover:bg-yellow-500 text-gray-900 font-bold py-2 px-6 rounded shadow">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$productosEdit = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Editar Producto') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('productos.update', $producto) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $producto->codigo) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unidad de Medida</label>
                            <input type="text" name="unidad_medida" value="{{ old('unidad_medida', $producto->unidad_medida) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio Base</label>
                            <input type="number" step="0.01" name="precio_base" value="{{ old('precio_base', $producto->precio_base) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('productos.index') }}" class="mr-3 text-gray-600 hover:text-gray-900 py-2">Cancelar</a>
                        <button type="submit" class="bg-fenix-gold hover:bg-yellow-500 text-gray-900 font-bold py-2 px-6 rounded shadow">Actualizar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

file_put_contents(__DIR__ . '/resources/views/clientes/index.blade.php', $clientesIndex);
file_put_contents(__DIR__ . '/resources/views/clientes/create.blade.php', $clientesCreate);
file_put_contents(__DIR__ . '/resources/views/clientes/edit.blade.php', $clientesEdit);
file_put_contents(__DIR__ . '/resources/views/productos/index.blade.php', $productosIndex);
file_put_contents(__DIR__ . '/resources/views/productos/create.blade.php', $productosCreate);
file_put_contents(__DIR__ . '/resources/views/productos/edit.blade.php', $productosEdit);

echo "Views generated correctly.\n";
