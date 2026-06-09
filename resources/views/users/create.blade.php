<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Añadir Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nombre de Usuario (Login)</label>
                        <input type="text" name="usuario" value="{{ old('usuario') }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('usuario')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border-gray-300">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Celular</label>
                        <input type="text" name="celular" class="mt-1 block w-full rounded-md border-gray-300" value="{{ old('celular') }}">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Rol Spatie</label>
                        <select name="role" required class="mt-1 block w-full rounded-md border-gray-300">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-fenix-gold font-bold py-2 px-6 rounded shadow">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>