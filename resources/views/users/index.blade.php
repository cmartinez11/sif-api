<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Gestión de Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Usuarios del Sistema</h3>
                    <a href="{{ route('users.create') }}" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-4 rounded shadow">
                        + Añadir Usuario
                    </a>
                </div>
                
                <table class="min-w-full divide-y divide-gray-200 mt-4">
                    <thead class="bg-fenix-green">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($users as $u)
                            <tr>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $u->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-750 font-mono">{{ $u->usuario ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $u->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-bold uppercase">{{ $u->roles->pluck('name')->join(', ') }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <a href="{{ route('users.edit', $u) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow text-sm transition">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>