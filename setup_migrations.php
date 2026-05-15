<?php
$files = [
    'create_agencias_table.php' => "            \$table->string('nombre');\n            \$table->string('direccion')->nullable();",
    'create_clientes_table.php' => "            \$table->string('nombre');\n            \$table->string('ruc', 11)->unique();\n            \$table->string('direccion')->nullable();\n            \$table->string('condicion_pago')->nullable();\n            \$table->string('provincia')->nullable();",
    'create_productos_table.php' => "            \$table->string('codigo')->unique();\n            \$table->string('nombre');\n            \$table->string('unidad_medida')->nullable();\n            \$table->decimal('precio_base', 10, 2);",
    'create_plantillas_table.php' => "            \$table->string('nombre');",
    'create_cotizaciones_table.php' => "            \$table->string('numero')->unique();\n            \$table->foreignId('vendedora_id')->constrained('users');\n            \$table->foreignId('cliente_id')->constrained('clientes');\n            \$table->foreignId('plantilla_id')->constrained('plantillas');\n            \$table->enum('moneda', ['soles', 'dolares'])->default('soles');\n            \$table->string('estado')->default('Borrador');\n            \$table->date('fecha_emision');\n            \$table->decimal('subtotal', 10, 2)->default(0);\n            \$table->decimal('igv', 10, 2)->default(0);\n            \$table->decimal('total', 10, 2)->default(0);\n            \$table->text('observaciones')->nullable();",
    'create_cotizacion_items_table.php' => "            \$table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');\n            \$table->foreignId('producto_id')->constrained('productos');\n            \$table->json('campos_json')->nullable();\n            \$table->decimal('precio_unitario', 10, 5)->default(0);\n            \$table->decimal('precio_total', 10, 2)->default(0);",
    'create_pedidos_table.php' => "            \$table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');\n            \$table->string('estado')->default('Pendiente');\n            \$table->date('fecha_pedido');\n            \$table->date('fecha_entrega_estimada')->nullable();"
];

$dir = __DIR__ . '/database/migrations/';
$dirFiles = scandir($dir);

foreach ($files as $nameKey => $content) {
    foreach ($dirFiles as $file) {
        if (strpos($file, $nameKey) !== false) {
            $path = $dir . $file;
            $code = file_get_contents($path);
            $replacement = "\$table->id();\n" . $content;
            $code = preg_replace('/\$table->id\(\);/', $replacement, $code);
            file_put_contents($path, $code);
            echo "Updated \$file\n";
        }
    }
}
