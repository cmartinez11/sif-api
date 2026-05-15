@forelse ($contactos as $contacto)
    <tr class="hover:bg-gray-50 transition duration-150">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $contacto->nombre }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contacto->telefono ?? 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contacto->correo ?? 'N/A' }}</td>
        <td class="px-6 py-4 text-sm text-gray-600">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                {{ $contacto->clientes_count }} asociadas
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-3">
            <a href="{{ route('contactos.edit', $contacto) }}" class="text-fenix-green hover:text-indigo-900">Editar / Asignar</a>
            <form action="{{ route('contactos.destroy', $contacto) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este contacto? Sus clientes NO serán eliminados, pero quedarán sin contacto asociado.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center italic">No se encontraron contactos con ese criterio.</td>
    </tr>
@endforelse
