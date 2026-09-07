<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos fiscales y de contacto de la cuenta para transferencias SPEI.
     *
     * Hasta ahora el correo al que el cliente manda su comprobante estaba
     * escrito a mano en la vista del checkout, asi que cambiarlo exigia tocar
     * codigo. El RFC y la moneda ni siquiera existian.
     */
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->string('rfc', 20)->nullable()->after('accountClabe');
            $table->string('receiptEmail', 150)->nullable()->after('rfc');
            $table->string('currency', 10)->nullable()->after('receiptEmail');
        });

        // Se siembra lo que el checkout ya mostraba, para que el despliegue no
        // cambie nada a la vista hasta que el negocio lo ajuste.
        DB::table('transfers')->update([
            'receiptEmail' => 'ventas@macdelnorte.com',
            'currency' => 'MXN',
        ]);
    }

    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropColumn(['rfc', 'receiptEmail', 'currency']);
        });
    }
};
