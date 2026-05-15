<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Seleccionar Plantilla de Cotización') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-center text-lg font-bold text-gray-700 mb-6">Elige el tipo de cotización a crear</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($plantillas as $p)
                        <a href="{{ route('cotizaciones.create', ['plantilla_id' => $p->id]) }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-fenix-green hover:text-white transition group">
                            <h5 class="mb-2 text-2xl font-bold tracking-tight text-fenix-green group-hover:text-white">{{ $p->nombre }}</h5>
                            <p class="font-normal text-gray-700 group-hover:text-gray-200">Plantilla de cotización estándar para formato {{ $p->nombre }}.</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>