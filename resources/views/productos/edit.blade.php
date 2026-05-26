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
                        <!-- Código (Sólo Lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $producto->codigo) }}" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm cursor-not-allowed focus:border-gray-300 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Línea -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Línea</label>
                            <select name="linea" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                                <option value="">-- Seleccione Línea --</option>
                                @foreach($lineas as $l)
                                    <option value="{{ $l }}" {{ old('linea', $producto->linea) == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            @error('linea') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sublínea -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sublínea</label>
                            <select name="sublinea" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                                <option value="">-- Seleccione Sublínea --</option>
                                @foreach($sublineas as $sl)
                                    <option value="{{ $sl }}" {{ old('sublinea', $producto->sublinea) == $sl ? 'selected' : '' }}>{{ $sl }}</option>
                                @endforeach
                            </select>
                            @error('sublinea') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unidad de Medida -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unidad de Medida (Ej: Millennium, Kilo, etc)</label>
                            <input type="text" name="unidad_medida" value="{{ old('unidad_medida', $producto->unidad_medida) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                        </div>

                        <!-- Unidad de Medida Logística (Opcional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unidad de Medida Logística (Opcional)</label>
                            <input type="text" name="unidad_medida_logistica" value="{{ old('unidad_medida_logistica', $producto->unidad_medida_logistica) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('unidad_medida_logistica') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Precio Base -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio Base</label>
                            <input type="number" step="0.01" name="precio_base" value="{{ old('precio_base', $producto->precio_base) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('precio_base') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Peso (Nuevo) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Peso (kg)</label>
                            <input type="number" step="0.01" name="peso" value="{{ old('peso', $producto->peso) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('peso') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Estado (Nuevo) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <select name="estado" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                                <option value="1" {{ old('estado', $producto->estado) == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('estado', $producto->estado) == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @hasanyrole('Supervisor|Administrador')
                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock</label>
                            <input type="number" step="0.001" name="stock" value="{{ old('stock', $producto->stock) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-fenix-green focus:ring focus:ring-fenix-green focus:ring-opacity-50">
                            @error('stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endhasanyrole
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