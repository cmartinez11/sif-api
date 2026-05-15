<?php

$dir = __DIR__ . '/app/Models/';
$models = ['Agencia', 'Cliente', 'Producto', 'Plantilla', 'Cotizacion', 'CotizacionItem', 'Pedido'];

foreach ($models as $model) {
    if (file_exists($dir . $model . '.php')) {
        $content = file_get_contents($dir . $model . '.php');
        $replacement = "    use HasFactory;\n\n    protected \$guarded = [];";
        $content = str_replace('use HasFactory;', $replacement, $content);
        file_put_contents($dir . $model . '.php', $content);
        echo "Updated $model\n";
    }
}
