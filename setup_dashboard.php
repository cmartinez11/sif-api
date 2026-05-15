<?php

$dirUsers = __DIR__ . '/resources/views/users';
if (!is_dir($dirUsers)) mkdir($dirUsers, 0777, true);

$dashboardController = <<<'EOD'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = [];

        if ($user->hasAnyRole(['Supervisor', 'Administrador'])) {
            // Metrics
            $data['total_pedidos'] = Pedido::count();
            $data['total_cotizaciones'] = Cotizacion::count();
            
            // Vendedora con más pedidos
            $topVendedora = Cotizacion::where('estado', 'Convertida a Pedido')
                ->selectRaw('vendedora_id, count(id) as total')
                ->groupBy('vendedora_id')
                ->orderBy('total', 'desc')
                ->first();
            
            if ($topVendedora) {
                $data['top_vendedora'] = User::find($topVendedora->vendedora_id)->name;
                $data['top_vendedora_pedidos'] = $topVendedora->total;
            }

            // Producto Más Vendido
            $topProduct = CotizacionItem::selectRaw('producto_id, count(producto_id) as freq')
                ->groupBy('producto_id')
                ->orderBy('freq', 'desc')
                ->first();
            
            if ($topProduct) {
                $data['top_producto'] = $topProduct->producto->nombre ?? 'N/A';
            }
        }

        return view('dashboard', compact('data'));
    }
}
EOD;

$userController = <<<'EOD'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasRole('Administrador')) abort(403);
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (!auth()->user()->hasRole('Administrador')) abort(403);
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Administrador')) abort(403);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Usuario creado.');
    }
}
EOD;

$dashboardView = <<<'EOD'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-fenix-green leading-tight">
            {{ __('Panel de Control (Dashboard)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Bienvenid@, {{ auth()->user()->name }}</h3>
                <p class="text-gray-600 mb-8">Tienes el rol otorgado de: <span class="font-bold text-fenix-green">{{ auth()->user()->roles->pluck('name')->join(', ') }}</span></p>

                @if(auth()->user()->hasAnyRole(['Supervisor', 'Administrador']))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-fenix-green text-white p-6 rounded-lg shadow">
                        <h4 class="text-3xl font-bold">{{ $data['total_pedidos'] ?? 0 }}</h4>
                        <p class="text-sm opacity-80 uppercase tracking-wide mt-2">Pedidos Realizados</p>
                    </div>
                    <div class="bg-fenix-gold text-black p-6 rounded-lg shadow">
                        <h4 class="text-3xl font-bold">{{ $data['total_cotizaciones'] ?? 0 }}</h4>
                        <p class="text-sm opacity-80 uppercase tracking-wide mt-2">Cotizaciones Emitidas</p>
                    </div>
                    <div class="bg-white border-l-4 border-fenix-green text-gray-800 p-6 rounded-lg shadow">
                        <h4 class="text-xl font-bold">{{ $data['top_producto'] ?? 'N/A' }}</h4>
                        <p class="text-sm text-gray-500 uppercase tracking-wide mt-2">Producto Más Popular</p>
                    </div>
                </div>

                <!-- Gráficos Integrados con Chart.js según estructura.md -->
                <div class="w-full md:w-1/2 p-4 border rounded shadow bg-white">
                    <h4 class="font-bold text-gray-800 mb-4 text-center">Rendimiento: Vendedora Top</h4>
                    <div class="text-center">
                        <div class="inline-block w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center text-4xl mb-4 text-fenix-green">🏆</div>
                        <h5 class="text-xl font-bold">{{ $data['top_vendedora'] ?? 'Sin datos' }}</h5>
                        <p class="text-gray-500">{{ $data['top_vendedora_pedidos'] ?? 0 }} Pedidos cerrados</p>
                    </div>
                </div>
                @else
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-800">
                        Navega en el menú superior para acceder a Cotizaciones, Pedidos u otros módulos habilitados para tu rol.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$usersIndexView = <<<'EOD'
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Rol</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($users as $u)
                            <tr>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $u->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $u->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-bold uppercase">{{ $u->roles->pluck('name')->join(', ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
EOD;

$usersCreateView = <<<'EOD'
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
                        <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300">
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
EOD;

file_put_contents(__DIR__ . '/app/Http/Controllers/DashboardController.php', $dashboardController);
file_put_contents(__DIR__ . '/app/Http/Controllers/UserController.php', $userController);
file_put_contents(__DIR__ . '/resources/views/dashboard.blade.php', $dashboardView);
file_put_contents(__DIR__ . '/resources/views/users/index.blade.php', $usersIndexView);
file_put_contents(__DIR__ . '/resources/views/users/create.blade.php', $usersCreateView);

echo "Dashboard & Users Logic created.";
