<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tiempo de entrega por regla de envío.
 *
 * Antes el checkout mostraba el mismo texto fijo para todos los métodos
 * ("Entrega estimada 1–5 días hábiles"), escrito a mano en la vista: no se
 * podía diferenciar DHL de recoger en sucursal, ni ajustarlo sin tocar código.
 *
 * Se guarda como RANGO de días y no como texto libre para que el panel ofrezca
 * campos numéricos y la redacción quede en un solo sitio
 * (ShippingRule::deliveryLabel()). La nota aparte cubre avisos como
 * "Material sujeto a disponibilidad" sin obligar a escribir el tiempo a mano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_rules', function (Blueprint $table) {
            $table->unsignedSmallInteger('delivery_days_min')->nullable()->after('cost');
            $table->unsignedSmallInteger('delivery_days_max')->nullable()->after('delivery_days_min');
            $table->string('delivery_note', 120)->nullable()->after('delivery_days_max');
        });

        // Las reglas que ya existen conservan lo que el checkout venía
        // mostrando, para que nada cambie de aspecto hasta que el negocio
        // ajuste cada método desde el panel.
        DB::table('shipping_rules')->update([
            'delivery_days_min' => 1,
            'delivery_days_max' => 5,
        ]);
    }

    public function down(): void
    {
        Schema::table('shipping_rules', function (Blueprint $table) {
            $table->dropColumn(['delivery_days_min', 'delivery_days_max', 'delivery_note']);
        });
    }
};
