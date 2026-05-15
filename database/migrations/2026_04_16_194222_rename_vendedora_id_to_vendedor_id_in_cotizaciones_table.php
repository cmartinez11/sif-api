<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameVendedoraIdToVendedorIdInCotizacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Usar SQL crudo para evitar la dependencia de doctrine/dbal en Laravel 8
        DB::statement('ALTER TABLE cotizaciones CHANGE vendedora_id vendedor_id BIGINT UNSIGNED NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE cotizaciones CHANGE vendedor_id vendedora_id BIGINT UNSIGNED NOT NULL');
    }
}
