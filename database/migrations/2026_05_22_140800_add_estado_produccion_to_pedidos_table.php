<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoProduccionToPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega el campo 'estado_produccion' a la tabla 'pedidos'.
     * Valores permitidos: 'POR PRODUCIR', 'PRODUCIDO'.
     * Se define como VARCHAR(20) nullable para no afectar registros existentes.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('estado_produccion', 20)
                  ->nullable()
                  ->default(null)
                  ->after('estado')
                  ->comment('Estado de producción del pedido parcial: POR PRODUCIR | PRODUCIDO');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('estado_produccion');
        });
    }
}
