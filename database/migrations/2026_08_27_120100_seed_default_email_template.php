<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Inserta la plantilla default (category_id = null) para que el admin tenga
 * algo real que editar desde el día uno, en vez de una pantalla vacía. El
 * contenido es el mismo diseño de resources/views/emails/marketing-offer.blade.php
 * ya convertido a placeholders de texto (ver EmailTemplateRenderer):
 * {{nombre_cliente}}, {{categoria}}, {{productos}}, {{cupon_bloque}}.
 *
 * Es una migración de datos (no un seeder aparte) para que corra sola en
 * `php artisan migrate` sin depender de que alguien acuerde ejecutar seeders
 * en producción.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('email_templates')->whereNull('category_id')->exists()) {
            return;
        }

        $body = <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Oferta especial — Mac Del Norte</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;">
        <tr>
            <td align="center" style="padding:20px 0;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; max-width:600px; width:100%;">

                    <tr>
                        <td style="background-color:#00468c; padding:20px; text-align:center;">
                            <a href="https://www.macdelnorte.com/" style="font-size:20px; color:#ffffff; text-decoration:none;">Mac Del Norte</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 20px 8px;">
                            <h1 style="margin:0 0 8px; font-size:20px; color:#222222;">
                                Hola {{nombre_cliente}},
                            </h1>
                            <p style="margin:0; font-size:14px; color:#555555; line-height:1.5;">
                                Preparamos algunas recomendaciones en <strong>{{categoria}}</strong> pensando en tus compras anteriores.
                            </p>
                        </td>
                    </tr>

                    {{cupon_bloque}}

                    {{productos}}

                    <tr>
                        <td style="background-color:#00468c; color:#ffffff; text-align:center; padding:16px;">
                            <p style="margin:0; font-size:12px;">Todos los derechos reservados &copy; __YEAR__ Mac Del Norte</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

        // El body es texto plano sustituido con str_replace (no Blade) — el
        // año se hornea aquí como texto estático al momento de sembrar la
        // plantilla; el admin puede editarlo a mano en años futuros si quiere.
        $body = str_replace('__YEAR__', date('Y'), $body);

        DB::table('email_templates')->insert([
            'name' => 'Plantilla general (default)',
            'subject' => 'Ofertas especiales para ti — Mac Del Norte',
            'body' => $body,
            'category_id' => null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->whereNull('category_id')
            ->where('name', 'Plantilla general (default)')
            ->delete();
    }
};
