<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRechazoFieldsToCotizacionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->string('estado_item')->default('Activo');
            $table->text('motivo_rechazo')->nullable();
            $table->decimal('precio_competencia', 10, 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotizacion_items', function (Blueprint $table) {
            $table->dropColumn(['estado_item', 'motivo_rechazo', 'precio_competencia']);
        });
    }
}
