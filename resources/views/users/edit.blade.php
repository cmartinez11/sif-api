<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Editar Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Información del Usuario</h3>
                    <p class="text-sm text-gray-500 mt-1">Actualiza los datos del usuario. El correo electrónico no puede ser modificado por seguridad.</p>
                </div>

                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Nombre -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Nombre de Usuario</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                               class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm focus:border-fenix-green focus:outline-none focus:ring-2 focus:ring-fenix-green/20 transition" 
                               required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email (Bloqueado) -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Correo Electrónico (No editable)</label>
                        <input type="email" value="{{ $user->email }}" disabled 
                               class="w-full rounded-xl border border-gray-300 bg-gray-100 text-gray-500 px-4 py-2 text-sm cursor-not-allowed shadow-inner">
                        <p class="text-xs text-gray-400 mt-1 italic">El correo electrónico es el identificador único y no puede cambiarse.</p>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-8">
                        <label class="block text-gray-700 font-bold mb-2">Nueva Contraseña</label>
                        <input type="password" name="password" 
                               class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm focus:border-fenix-green focus:outline-none focus:ring-2 focus:ring-fenix-green/20 transition">
                        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3 text-fenix-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Deja este campo en blanco si no deseas cambiar la contraseña.
                        </p>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between border-t pt-6">
                        <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-700 font-medium transition">
                            Cancelar y Volver
                        </a>
                        <button type="submit" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-6 rounded-full shadow-lg transition transform hover:scale-105">
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
