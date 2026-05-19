<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RestructurePedidosAndCreatePedidoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 0. Ensure 'users' table has a primary key on 'id'
        try {
            $hasUserKey = DB::select("
                SELECT 1 FROM pg_constraint 
                WHERE conrelid = 'users'::regclass AND contype = 'p'
            ");
            if (empty($hasUserKey)) {
                DB::statement('ALTER TABLE users ADD PRIMARY KEY (id)');
            }
        } catch (\Exception $e) {
            // Ignore if constraint already exists
        }

        // 0.1 Ensure 'pedidos' table has a primary key on 'id'
        try {
            $hasPedidoKey = DB::select("
                SELECT 1 FROM pg_constraint 
                WHERE conrelid = 'pedidos'::regclass AND contype = 'p'
            ");
            if (empty($hasPedidoKey)) {
                DB::statement('ALTER TABLE pedidos ADD PRIMARY KEY (id)');
            }
        } catch (\Exception $e) {
            // Ignore if constraint already exists
        }

        // 1. Restructure 'pedidos' table
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'user_id')) {
                $table->foreignId('user_id')->nullable()->index()->after('cotizacion_id')->constrained('users')->onDelete('set null');
            }
        });

        // Make 'cotizacion_id' nullable using raw SQL for PostgreSQL
        DB::statement('ALTER TABLE pedidos ALTER COLUMN cotizacion_id DROP NOT NULL');

        // 2. Create 'pedido_items' table
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('unidad_medida');
            $table->decimal('precio_unitario', 12, 4)->default(0);
            $table->decimal('precio_total', 12, 2)->default(0);
            $table->json('campos_json')->nullable();
            $table->timestamps();
        });

        // 3. Migrate existing data
        $existingPedidos = DB::table('pedidos')->get();
        foreach ($existingPedidos as $pedido) {
            if (!$pedido->cotizacion_id) {
                continue;
            }

            $cotizacion = DB::table('cotizaciones')->where('id', $pedido->cotizacion_id)->first();
            if ($cotizacion) {
                // Populate user_id from cotizacion vendedor_id
                DB::table('pedidos')->where('id', $pedido->id)->update([
                    'user_id' => $cotizacion->vendedor_id
                ]);

                // Copy non-rejected items to pedido_items
                $items = DB::table('cotizacion_items')
                    ->where('cotizacion_id', $cotizacion->id)
                    ->where('estado_item', '!=', 'Rechazado')
                    ->get();

                foreach ($items as $item) {
                    $producto = DB::table('productos')->where('id', $item->producto_id)->first();
                    $unidadMedida = $producto->unidad_medida ?? 'Und';

                    DB::table('pedido_items')->insert([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $item->producto_id,
                        'unidad_medida' => $unidadMedida,
                        'precio_unitario' => $item->precio_unitario,
                        'precio_total' => $item->precio_total,
                        'campos_json' => $item->campos_json,
                        'created_at' => $pedido->created_at ?? now(),
                        'updated_at' => $pedido->updated_at ?? now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Drop 'pedido_items' table
        Schema::dropIfExists('pedido_items');

        // 2. Rollback 'pedidos' table changes
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        // Restore NOT NULL constraint on cotizacion_id if possible
        try {
            DB::statement('ALTER TABLE pedidos ALTER COLUMN cotizacion_id SET NOT NULL');
        } catch (\Exception $e) {
            // Log or ignore if data constraints prevent it
        }
    }
}
