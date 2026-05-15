<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('linea')->nullable()->after('stock');
            $table->string('sublinea')->nullable()->after('linea');
            $table->boolean('estado')->default(true)->after('unidad_medida_logistica');
            $table->decimal('peso', 8, 3)->nullable()->after('estado');
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
            $table->dropColumn(['linea', 'sublinea','estado','peso']);
        });
    }
}
