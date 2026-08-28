<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plantillas de correo editables desde el admin (módulo hermano de
 * marketing_api_tokens — mismo permiso "marketing-integracion"). Reemplazan
 * la vista Blade fija resources/views/emails/marketing-offer.blade.php,
 * consumidas por MarketingDataController::email() vía EmailTemplateRenderer.
 *
 * category_id nulo = plantilla "general/default", usada cuando no hay una
 * específica para la categoría dominante del cliente (o cuando el cliente no
 * tiene categoría dominante). nullOnDelete() para que borrar una categoría
 * no tumbe la plantilla — solo la vuelve general.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
