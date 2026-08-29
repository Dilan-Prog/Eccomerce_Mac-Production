<?php

namespace App\Console\Commands;

use App\Support\SequenceProcessor;
use Illuminate\Console\Command;

/**
 * Disparo MANUAL del housekeeping de secuencias (App\Support\SequenceProcessor).
 *
 * IMPORTANTE: este comando NO está registrado en app/Console/Kernel.php y no
 * debe registrarse. El reloj del módulo de email marketing es n8n: el mismo
 * proceso corre solo, en línea, cada vez que n8n llama
 * GET /api/marketing/sequences/due (ver MarketingSequenceController::due).
 * Este comando existe únicamente para poder ver el efecto del proceso desde
 * la terminal al probar, sin tener que armar la llamada HTTP con su token.
 */
class ProcessEmailSequences extends Command
{
    protected $signature = 'app:process-email-sequences';

    protected $description = 'Corre a mano el housekeeping de secuencias de correo (inscribir, sacar por compra, vencer pasos, cerrar). Solo para pruebas: en producción lo dispara n8n vía GET /api/marketing/sequences/due.';

    public function handle(SequenceProcessor $processor): int
    {
        $result = $processor->process();

        $this->info('Housekeeping de secuencias terminado:');
        $this->line('  Inscripciones nuevas: ' . $result['enrolled']);
        $this->line('  Salidas por compra:   ' . $result['exited']);
        $this->line('  Pasos vencidos:       ' . $result['promoted']);
        $this->line('  Inscripciones cerradas: ' . $result['completed']);

        return self::SUCCESS;
    }
}
