<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_type')->default('personal')->after('rfc'); // personal | b2b
            $table->string('tipo_cliente')->nullable()->after('account_type');  // revendedor | tecnico | empresa | contratista
            $table->string('giro_industrial')->nullable()->after('tipo_cliente');
            $table->string('volumen_mensual')->nullable()->after('giro_industrial');
            $table->string('ciudad')->nullable()->after('volumen_mensual');
            $table->string('csf_path')->nullable()->after('ciudad');           // ruta al PDF de CSF
            $table->string('b2b_status')->default('pending')->after('csf_path'); // pending | verified | rejected
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'tipo_cliente', 'giro_industrial', 'volumen_mensual', 'ciudad', 'csf_path', 'b2b_status']);
        });
    }
};
