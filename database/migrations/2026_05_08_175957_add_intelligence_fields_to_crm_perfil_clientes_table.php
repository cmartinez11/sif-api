<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntelligenceFieldsToCrmPerfilClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_perfil_clientes', function (Blueprint $table) {
            $table->string('proveedor_actual')->nullable()->after('frecuencia_compra');
            $table->string('problemas_proveedor')->nullable()->after('proveedor_actual');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_perfil_clientes', function (Blueprint $table) {
            $table->dropColumn(['proveedor_actual', 'problemas_proveedor']);
        });
    }
}
