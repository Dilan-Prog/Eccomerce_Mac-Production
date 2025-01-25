<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CopySkuToModelo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:copy-sku-to-modelo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

     public function __construct()
    {
        parent::__construct();
    }
    public function handle()
    {
        // Nombre de la tabla que quieres modificar
        $table = 'products';

        try {
            // Actualizar el campo short_description para todos los registros a 'none'
            DB::table($table)->update(['short_description' => 'Garantia:']);

            $this->info('El campo short_description ha sido actualizado a "none" para todos los productos.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}