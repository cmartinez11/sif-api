<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmPerfilClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_perfil_clientes', function (Blueprint $table) {
            $table->id();
        
        // 1. Definimos la columna con el tipo exacto (Big Integer)
            $table->unsignedBigInteger('cliente_id');

            // 2. Checklist y demás campos
            $table->string('tipo_preforma')->nullable(); 
            $table->string('gramaje')->nullable();
            $table->string('cuello')->nullable();
            $table->string('aplicacion')->nullable();
            $table->integer('cant_maquinas')->default(0);
            $table->decimal('vol_mensual', 15, 2)->default(0);
            $table->decimal('vol_proyectado', 15, 2)->default(0);
            $table->string('frecuencia_compra')->nullable();
            $table->boolean('urgencias_frecuentes')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // 3. Definición manual de la llave foránea
            $table->foreign('cliente_id')
                ->references('id')
                ->on('clientes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_perfil_clientes');
    }
}
