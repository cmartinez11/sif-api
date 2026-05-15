<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:clean-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza un reinicio casi total del sistema para producción, conservando solo datos esenciales.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Mensaje de advertencia claro solicitado por el usuario
        if ($this->confirm('¡CUIDADO! Estás a punto de borrar clientes, productos, pedidos y usuarios. ¿Deseas continuar?', false)) {
            $this->info('Iniciando limpieza del sistema...');

            try {
                // Desactivar llaves foráneas temporalmente (PostgreSQL)
                DB::statement('SET session_replication_role = replica;');

                // 1. Borrar toda la data transaccional
                $this->line('Limpiando datos transaccionales...');
                DB::table('pedidos')->truncate();
                DB::table('cotizacion_items')->truncate();
                DB::table('cotizaciones')->truncate();
                DB::table('crm_competencia')->truncate();
                DB::table('crm_perfil_clientes')->truncate();

                // 2. Borrar Catálogos (Productos, Clientes, Contactos)
                $this->line('Limpiando catálogos...');
                DB::table('productos')->truncate();
                DB::table('contactos')->truncate();
                DB::table('clientes')->truncate();

                // 3. Borrar todos los Usuarios, EXCEPTO el Administrador Principal (ID 1)
                $this->line('Limpiando usuarios...');
                DB::table('users')->where('id', '!=', 1)->delete();

                // Activar llaves foráneas nuevamente
                DB::statement('SET session_replication_role = DEFAULT;');

                $this->info('¡Sistema limpiado con éxito!');
            } catch (\Exception $e) {
                // Asegurarse de restaurar el estado de las llaves foráneas en caso de error
                DB::statement('SET session_replication_role = DEFAULT;');
                $this->error('Error durante la limpieza: ' . $e->getMessage());
                return 1;
            }

            return 0;
        }

        $this->info('Operación cancelada.');
        return 0;
    }
}
