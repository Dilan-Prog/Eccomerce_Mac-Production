<?php
// database/migrations/2026_04_27_000002_create_service_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('folio')->unique();
            $table->date('fecha_servicio')->nullable();
            $table->enum('tipo_servicio', [
                'Calibración',
                'Reparación',
                'Mantenimiento Preventivo',
                'Mantenimiento Correctivo',
                'Instalación',
                'Diagnóstico',
            ])->nullable();
            $table->string('tecnico_nombre')->nullable();
            // Cliente
            $table->string('cliente_nombre')->nullable();
            $table->string('cliente_empresa')->nullable();
            $table->string('cliente_rfc')->nullable();
            $table->text('cliente_direccion')->nullable();
            $table->string('cliente_telefono')->nullable();
            $table->string('cliente_email')->nullable();
            // Equipo
            $table->string('equipo_descripcion')->nullable();
            $table->string('equipo_marca')->nullable();
            $table->string('equipo_modelo')->nullable();
            $table->string('equipo_serie')->nullable();
            $table->string('equipo_ubicacion_tag')->nullable();
            // Cuerpo del reporte
            $table->json('mediciones')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->longText('firma_tecnico')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
