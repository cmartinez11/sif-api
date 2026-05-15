<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Editar Contacto / Asignar Clientes') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 bg-white border-b border-gray-200">
                    <form action="{{ route('contactos.update', $contacto) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-bold text-fenix-green mb-4">Información del Contacto</h3>
                                <hr class="mb-4">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $contacto->nombre) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-fenix-green focus:border-fenix-green" required>
                                @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $contacto->telefono) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-fenix-green focus:border-fenix-green">
                                @error('telefono') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="correo" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                <input type="email" name="correo" id="correo" value="{{ old('correo', $contacto->correo) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-fenix-green focus:border-fenix-green">
                                @error('correo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="md:col-span-2 mt-10">
                            <h3 class="text-lg font-bold text-fenix-green mb-2">Asignar Razones Sociales (Clientes)</h3>
                            <p class="text-sm text-gray-500 mb-4">Selecciona los clientes que estarán asociados a este contacto.</p>
                            <hr class="mb-4">
                            
                            <div class="max-h-96 overflow-y-auto border rounded p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($clientes as $cli)
                                        <div class="flex items-center p-2 bg-white rounded border hover:border-fenix-gold transition">
                                            <input type="checkbox" name="client_ids[]" value="{{ $cli->id }}" 
                                                   id="client_{{ $cli->id }}"
                                                   {{ $cli->contacto_id == $contacto->id ? 'checked' : '' }}
                                                   class="h-4 w-4 text-fenix-green focus:ring-fenix-gold border-gray-300 rounded">
                                            <label for="client_{{ $cli->id }}" class="ml-2 block text-sm text-gray-700 overflow-hidden text-ellipsis whitespace-nowrap">
                                                {{ $cli->nombre }} 
                                                <span class="text-xs text-gray-400 block">{{ $cli->ruc }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-4">
                            <a href="{{ route('contactos.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded shadow transition">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-8 rounded shadow transition">
                                Guardar Cambios y Asignaciones
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
