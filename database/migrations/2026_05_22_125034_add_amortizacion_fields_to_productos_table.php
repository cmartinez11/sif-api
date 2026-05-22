<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmortizacionFieldsToProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('deuda_arrastrada', 12, 3)->default(0.000)->after('stock');
            $table->date('ultimo_stock_cargado_at')->nullable()->after('deuda_arrastrada');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['deuda_arrastrada', 'ultimo_stock_cargado_at']);
        });
    }
}
