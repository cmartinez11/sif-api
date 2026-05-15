<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUniqueConstraintToClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the unique constraint to id
        DB::statement('ALTER TABLE clientes ADD CONSTRAINT clientes_id_unique UNIQUE (id);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE clientes DROP CONSTRAINT IF EXISTS clientes_id_unique;');
    }
}
