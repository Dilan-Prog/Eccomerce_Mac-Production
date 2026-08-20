<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->string('tiempo_entrega_general', 255)->nullable()->after('total')
                ->comment('Texto libre, capturado por el vendedor — se imprime en el encabezado del PDF. Sin valor por defecto: si está vacío, no se imprime nada.');
            $table->string('nota_envio', 255)->nullable()->after('tiempo_entrega_general')
                ->comment('Texto libre, capturado por el vendedor — se imprime como leyenda al pie del PDF (ej. condiciones de envío). Sin valor por defecto.');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['tiempo_entrega_general', 'nota_envio']);
        });
    }
};
