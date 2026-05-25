<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('accion'); // 'CREAR', 'MODIFICAR', 'APROBAR', 'ELIMINAR'
            $table->string('modulo'); // 'Pedidos', 'Cotizaciones', 'Productos'
            $table->unsignedBigInteger('registro_id');
            $table->text('descripcion');
            $table->json('historial_json')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
