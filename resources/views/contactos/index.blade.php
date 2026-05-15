<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestión de Contactos') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900 border-l-4 border-fenix-gold pl-3">Listado de Contactos</h3>
                        <a href="{{ route('contactos.create') }}" class="bg-fenix-green hover:bg-[#12311f] text-white font-bold py-2 px-4 rounded shadow transition ease-in-out duration-150">
                            + Añadir Contacto
                        </a>
                    </div>
                    
                    <div x-data="{ 
                        search: '', 
                        loading: false,
                        initialRows: '',
                        fetchContactos() {
                            if (this.search.trim() === '') {
                                document.getElementById('table-body').innerHTML = this.initialRows;
                                return;
                            }
                            this.loading = true;
                            fetch(`{{ route('contactos.search') }}?search=${this.search}`)
                                .then(response => response.text())
                                .then(html => {
                                    document.getElementById('table-body').innerHTML = html;
                                    this.loading = false;
                                });
                        }
                    }" x-init="initialRows = document.getElementById('table-body').innerHTML">
                        <div class="mb-5 flex justify-end">
                            <div class="relative w-full sm:w-1/2 lg:w-1/3">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input 
                                    id="Contactso"
                                    x-model.debounce.300ms="search"
                                    x-on:input="fetchContactos"
                                    type="text" 
                                    class="block w-full pl-10 pr-3 py-3 sm:py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-fenix-green focus:border-fenix-green text-sm transition duration-150 ease-in-out" 
                                    placeholder="Buscar por nombre, empresa o teléfono..."
                                >
                                <div x-show="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" x-cloak>
                                    <svg class="animate-spin h-5 w-5 text-fenix-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
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
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Teléfono</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Correo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Razones Sociales</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body" class="bg-white divide-y divide-gray-200">
                                    @include('contactos._table_rows')
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6" x-show="search === ''">
                            {{ $contactos->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

