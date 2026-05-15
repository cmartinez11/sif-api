<?php

use Illuminate\Support\Facades\DB;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::beginTransaction();

    // 1. Limpiar cotizaciones "basura" (128, 129)
    $trashIds = [128, 129];
    DB::table('cotizacion_items')->whereIn('cotizacion_id', $trashIds)->delete();
    DB::table('cotizaciones')->whereIn('id', $trashIds)->delete();
    echo "Cotizaciones 128 y 129 (y sus items) eliminadas.\n";

    // 2. Reasignar ID 127 -> 1
    // Primero actualizamos los items para que no queden huérfanos (aunque PostgreSQL podría tener constraints)
    DB::table('cotizacion_items')->where('cotizacion_id', 127)->update(['cotizacion_id' => 1]);
    DB::table('cotizaciones')->where('id', 127)->update(['id' => 1]);
    echo "Cotización 127 reasignada al ID 1.\n";

    // 3. Resetear la secuencia de la tabla cotizaciones a 1
    DB::statement("SELECT setval('cotizaciones_id_seq', 1)");
    echo "Secuencia 'cotizaciones_id_seq' reseteada a 1. El próximo ID será 2.\n";

    DB::commit();
    echo "Operación completada con éxito.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
