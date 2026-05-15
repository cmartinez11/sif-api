<?php

use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'cotizaciones' => 'cotizaciones_id_seq',
    'productos'    => 'productos_id_seq',
    'clientes'     => 'clientes_id_seq',
];

foreach ($tables as $table => $sequence) {
    try {
        // Obtenemos el MAX(id)
        $maxId = DB::table($table)->max('id') ?? 0;
        
        // Sincronizamos la secuencia
        DB::statement("SELECT setval('$sequence', $maxId)");
        
        echo "Secuencia de '$table' ($sequence) reseteada a $maxId.\n";
    } catch (\Exception $e) {
        echo "Error al resetear la secuencia de '$table': " . $e->getMessage() . "\n";
    }
}
