<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeoToClientesAndLinkToContactos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'provincia')) {
                $table->string('provincia')->nullable()->after('direccion');
            }
            $table->string('distrito')->nullable()->after('provincia');
            $table->string('departamento')->nullable()->after('distrito');
        });

        Schema::table('contactos', function (Blueprint $table) {
            $table->string('enlace')->nullable()->after('correo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['distrito', 'departamento']);
            // We don't drop provincia if it was there before, but for simplicity in this migration context:
            // if we want to be safe we could check if we created it here.
        });

        Schema::table('contactos', function (Blueprint $table) {
            $table->dropColumn('enlace');
        });
    }
}
