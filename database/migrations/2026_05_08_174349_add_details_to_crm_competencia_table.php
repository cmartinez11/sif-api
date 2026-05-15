<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToCrmCompetenciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_competencia', function (Blueprint $table) {
            $table->string('motivo_perdida')->nullable()->after('precio_ofrecido');
            $table->string('entrega_proveedor')->nullable()->after('motivo_perdida');
            $table->string('entrega_nuestra')->nullable()->after('entrega_proveedor');
            $table->text('detalle_perdida')->nullable()->after('entrega_nuestra');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_competencia', function (Blueprint $table) {
            $table->dropColumn(['motivo_perdida', 'entrega_proveedor', 'entrega_nuestra', 'detalle_perdida']);
        });
    }
}
