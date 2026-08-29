<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Plantilla de correo editable desde el admin (ver
 * app/Http/Controllers/Backend/EmailTemplateController.php). Consumida por
 * App\Support\EmailTemplateRenderer + MarketingDataController::email() en
 * vez de la vista Blade fija resources/views/emails/marketing-offer.blade.php.
 *
 * category_id null = plantilla general/default (fallback cuando no hay una
 * específica para la categoría dominante del cliente).
 *
 * DECISIÓN: `body` se queda con ese nombre (no se renombra a `html_body`)
 * aunque conceptualmente sea el "html_body" del motor de plantillas — ya lo
 * usan BlockEmailRenderer, EmailTemplateRenderer, el controlador, las vistas
 * y el seeder; renombrarla arriesgaba romper todo eso sin ganar nada.
 *
 * builder_mode: solo informativo — qué editor se usó por última vez
 * ('code' o 'blocks'), no afecta el renderizado (eso lo decide blocks_json).
 * is_system/system_key: plantillas protegidas por código (no se pueden
 * borrar desde el admin, ver EmailTemplateController::destroy(), pero sí
 * editar) — para identificar plantillas futuras atadas a un flujo del
 * sistema (ej. "cotización enviada").
 */
class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'body',
        'blocks_json',
        'category_id',
        'status',
        'builder_mode',
        'is_system',
        'system_key',
    ];

    protected $casts = [
        'status' => 'boolean',
        'blocks_json' => 'array',
        'is_system' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
