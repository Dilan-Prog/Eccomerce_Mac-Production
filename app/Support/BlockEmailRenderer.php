<?php

namespace App\Support;

use App\Models\Cotizacion;

/**
 * Renderiza el HTML final de una plantilla de correo armada con el editor
 * visual por bloques (JSON guardado en email_templates.blocks_json — ver
 * App\Models\EmailTemplate). Contrato de datos compartido con el editor
 * visual (JS/Blade, construido en paralelo, ver
 * resources/views/admin-ui/email-templates/form.blade.php):
 *
 *   { "theme": { "backgroundColor": "#RRGGBB" },
 *     "blocks": [ { "id": "b1", "type": "logo|heading|text|products|coupon|
 *                   button|divider|spacer|footer", "content": "...",
 *                   "settings": { ... } }, ... ] }
 *
 * Igual que resources/views/emails/marketing-offer.blade.php: 100% tablas
 * con estilos en línea (sin CSS externo ni flex/grid), para compatibilidad
 * con clientes de correo.
 *
 * DECISIÓN DE DISEÑO (importante para quien conecte el editor visual o
 * modifique el flujo de envío): este renderer NO depende directamente de
 * App\Support\MarketingOfferBuilder (que hace queries a la base de datos
 * para resolver productos/cupón de un cliente real). En vez de eso, los
 * bloques dinámicos "products"/"coupon" toman su HTML ya armado desde
 * $placeholderData, usando EXACTAMENTE las mismas claves que ya produce
 * MarketingOfferBuilder::placeholderData(): 'productos' y 'cupon_bloque'.
 * Así:
 *   - El flujo real (MarketingDataController::email()) sigue llamando a
 *     MarketingOfferBuilder::placeholderData($user, $offer) UNA sola vez y
 *     pasa ese mismo array tanto a self::render() (para los bloques
 *     dinámicos) como, después, a EmailTemplateRenderer::render() (para
 *     sustituir los marcadores {{...}} de texto) — sin duplicar lógica de
 *     negocio ni hacer queries extra.
 *   - El guardado del formulario admin y el endpoint de previsualización en
 *     vivo (sin cliente real de por medio) usan self::dummyPlaceholderData(),
 *     con HTML de ejemplo fabricado a mano, sin tocar la base de datos.
 *
 * Los marcadores {{nombre_cliente}}, {{categoria}}, {{cupon_codigo}},
 * {{cupon_descuento}} dentro del "content" de bloques heading/text/footer
 * NO se sustituyen aquí — se dejan tal cual en el HTML de salida para que
 * App\Support\EmailTemplateRenderer los sustituya después, reutilizando el
 * mismo mecanismo que ya usa sobre el campo `body` plano.
 */
class BlockEmailRenderer
{
    private const DEFAULT_CONTAINER_BG = '#F4F6F8';
    private const DEFAULT_CONTENT_BG = '#FFFFFF';

    /** URL fija del logo del sitio (no vive en GeneralSetting). */
    private const LOGO_PATH = 'uploads/logo/2k-blanco-azul.png';

    /**
     * @param  array{theme?: array{backgroundColor?: string}, blocks?: array<int, array<string, mixed>>}  $blocksJson
     * @param  array{nombre_cliente?: string, categoria?: string, productos?: string, cupon_codigo?: string, cupon_descuento?: string, cupon_bloque?: string}  $placeholderData
     */
    public function render(array $blocksJson, array $placeholderData): string
    {
        $containerBg = $blocksJson['theme']['backgroundColor'] ?? self::DEFAULT_CONTAINER_BG;
        $blocks = is_array($blocksJson['blocks'] ?? null) ? $blocksJson['blocks'] : [];

        $rows = '';
        foreach ($blocks as $block) {
            if (!is_array($block) || empty($block['type'])) {
                continue;
            }
            $rows .= $this->renderBlock($block, $placeholderData);
        }

        return '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mac Del Norte</title>
</head>
<body style="margin:0; padding:0; background-color:' . $this->attr($containerBg) . '; font-family: Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:' . $this->attr($containerBg) . ';">
<tr>
<td align="center" style="padding:20px 0;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:' . self::DEFAULT_CONTENT_BG . '; max-width:600px; width:100%;">
' . $rows . '
</table>
</td>
</tr>
</table>
</body>
</html>';
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  array<string, mixed>  $placeholderData
     */
    private function renderBlock(array $block, array $placeholderData): string
    {
        $settings = is_array($block['settings'] ?? null) ? $block['settings'] : [];
        $content = (string) ($block['content'] ?? '');

        return match ($block['type']) {
            'logo' => $this->renderLogo($settings),
            'heading' => $this->renderHeading($content, $settings),
            'text' => $this->renderRichText($content, $settings, false),
            'products' => $this->renderProducts($settings, $placeholderData),
            'coupon' => $this->renderCoupon($settings, $placeholderData),
            'button' => $this->renderButton($content, $settings),
            'divider' => $this->renderDivider($settings),
            'spacer' => $this->renderSpacer($settings),
            'footer' => $this->renderRichText($content, $settings, true),
            default => '',
        };
    }

    /** @param  array<string, mixed>  $settings */
    private function renderLogo(array $settings): string
    {
        $align = $this->align($settings['align'] ?? 'center', 'center');
        $width = (int) ($settings['width'] ?? 160);
        $width = $width > 0 ? $width : 160;
        $bg = (string) ($settings['backgroundColor'] ?? self::DEFAULT_CONTENT_BG);
        $logoUrl = asset(self::LOGO_PATH);

        return '<tr><td align="' . $align . '" style="background-color:' . $this->attr($bg) . '; padding:20px;">'
            . '<img src="' . $this->attr($logoUrl) . '" alt="Mac Del Norte" width="' . $width . '" style="display:block; width:' . $width . 'px; height:auto; border:0;">'
            . '</td></tr>';
    }

    /** @param  array<string, mixed>  $settings */
    private function renderHeading(string $content, array $settings): string
    {
        $align = $this->align($settings['align'] ?? 'left', 'left');
        $color = (string) ($settings['color'] ?? '#0B0B0B');
        $bg = (string) ($settings['backgroundColor'] ?? self::DEFAULT_CONTENT_BG);
        $fontSize = (int) ($settings['fontSize'] ?? 22);
        $fontSize = $fontSize > 0 ? $fontSize : 22;

        return '<tr><td style="background-color:' . $this->attr($bg) . '; padding:16px 20px; text-align:' . $align . ';">'
            . '<h2 style="margin:0; font-size:' . $fontSize . 'px; line-height:1.3; color:' . $this->attr($color) . '; font-weight:bold;">'
            . e($content)
            . '</h2></td></tr>';
    }

    /**
     * Compartido por 'text' y 'footer' — misma estructura, defaults
     * distintos (footer más chico, centrado y en gris).
     *
     * @param  array<string, mixed>  $settings
     */
    private function renderRichText(string $content, array $settings, bool $isFooter): string
    {
        $color = (string) ($settings['color'] ?? ($isFooter ? '#888888' : '#333333'));
        $bg = (string) ($settings['backgroundColor'] ?? self::DEFAULT_CONTENT_BG);
        $fontSize = $isFooter ? '12px' : '14px';
        $html = $this->decodeIfEntityEncoded($content);

        return '<tr><td style="background-color:' . $this->attr($bg) . '; padding:16px 20px; color:' . $this->attr($color) . '; font-size:' . $fontSize . '; line-height:1.5;' . ($isFooter ? ' text-align:center;' : '') . '">'
            . $html
            . '</td></tr>';
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $placeholderData
     */
    private function renderProducts(array $settings, array $placeholderData): string
    {
        $productsHtml = (string) ($placeholderData['productos'] ?? '');
        if (trim($productsHtml) === '') {
            return '';
        }

        $bg = (string) ($settings['backgroundColor'] ?? self::DEFAULT_CONTENT_BG);

        return '<tr><td style="background-color:' . $this->attr($bg) . '; padding:8px 0;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $productsHtml . '</table>'
            . '</td></tr>';
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<string, mixed>  $placeholderData
     */
    private function renderCoupon(array $settings, array $placeholderData): string
    {
        $couponHtml = (string) ($placeholderData['cupon_bloque'] ?? '');
        if (trim($couponHtml) === '') {
            // Sin cupón disponible: no se muestra nada, igual que
            // {{cupon_bloque}} en las plantillas de texto plano.
            return '';
        }

        $bg = (string) ($settings['backgroundColor'] ?? self::DEFAULT_CONTENT_BG);

        return '<tr><td style="background-color:' . $this->attr($bg) . '; padding:8px 0;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $couponHtml . '</table>'
            . '</td></tr>';
    }

    /** @param  array<string, mixed>  $settings */
    private function renderButton(string $content, array $settings): string
    {
        $align = $this->align($settings['align'] ?? 'center', 'center');
        $bg = (string) ($settings['backgroundColor'] ?? '#0B4C87');
        $textColor = (string) ($settings['textColor'] ?? '#FFFFFF');
        $url = (string) ($settings['url'] ?? '#');
        $label = trim($content) !== '' ? $content : 'Ver más';

        return '<tr><td style="padding:16px 20px; text-align:' . $align . ';">'
            . '<a href="' . $this->attr($url) . '" style="display:inline-block; padding:12px 24px; background-color:' . $this->attr($bg) . '; color:' . $this->attr($textColor) . '; text-decoration:none; font-size:14px; font-weight:bold; border-radius:4px;">'
            . e($label)
            . '</a></td></tr>';
    }

    /** @param  array<string, mixed>  $settings */
    private function renderDivider(array $settings): string
    {
        $color = (string) ($settings['color'] ?? '#DDDDDD');

        return '<tr><td style="padding:12px 20px;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px solid ' . $this->attr($color) . '; font-size:0; line-height:0;">&nbsp;</td></tr></table>'
            . '</td></tr>';
    }

    /** @param  array<string, mixed>  $settings */
    private function renderSpacer(array $settings): string
    {
        $height = (int) ($settings['height'] ?? 20);
        $height = $height > 0 ? $height : 20;

        return '<tr><td style="height:' . $height . 'px; line-height:' . $height . 'px; font-size:0;">&nbsp;</td></tr>';
    }

    private function align(mixed $value, string $default): string
    {
        $value = is_string($value) ? $value : $default;

        return in_array($value, ['left', 'center', 'right'], true) ? $value : $default;
    }

    /**
     * Defensivo: si el "content" de un bloque text/footer llega con las
     * etiquetas HTML entity-encoded (ej. "&lt;p&gt;...&lt;/p&gt;") en vez de
     * HTML crudo, lo decodifica — así funciona sin importar cuál de las dos
     * formas mande el editor visual del otro agente.
     */
    private function decodeIfEntityEncoded(string $content): string
    {
        if ($content !== '' && str_contains($content, '&lt;') && !str_contains($content, '<')) {
            return html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
        }

        return $content;
    }

    private function attr(mixed $value): string
    {
        return e((string) $value);
    }

    /**
     * Datos de ejemplo (sin tocar la base de datos) para los bloques
     * dinámicos "products"/"coupon" y los marcadores de texto, usados
     * cuando no hay un cliente real de por medio:
     *   - Al guardar la plantilla desde el admin, para dejar `body` con una
     *     vista previa razonable (ver EmailTemplateController).
     *   - En el endpoint de previsualización en vivo del editor visual
     *     (POST /admin/email-templates/preview-blocks).
     *
     * @return array{nombre_cliente: string, categoria: string, productos: string, cupon_codigo: string, cupon_descuento: string, cupon_bloque: string}
     */
    public static function dummyPlaceholderData(): array
    {
        $productCard = static function (string $name, string $price): string {
            return '<tr>
                <td style="padding:0 20px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:6px;">
                        <tr>
                            <td width="110" style="padding:12px; vertical-align:top; background-color:#f0f0f0; text-align:center; color:#999999; font-size:11px;">Imagen</td>
                            <td style="padding:12px 12px 12px 0; vertical-align:top;">
                                <p style="margin:0 0 6px; font-size:15px; color:#222222; font-weight:bold;">' . e($name) . '</p>
                                <p style="margin:0 0 10px; font-size:16px; color:#00468c; font-weight:bold;">' . e($price) . '</p>
                                <a href="#" style="display:inline-block; padding:8px 14px; background-color:#00468c; color:#ffffff; text-decoration:none; font-size:13px; border-radius:4px;">Ver producto</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>';
        };

        $productos = $productCard('Laptop Ejemplo 14"', '$14,999.00 MXN')
            . $productCard('Mouse inalámbrico Ejemplo', '$399.00 MXN');

        $cuponBloque = '<tr>
            <td style="padding:16px 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#00468c; border-radius:6px;">
                    <tr>
                        <td style="padding:16px; text-align:center; color:#ffffff;">
                            <p style="margin:0 0 6px; font-size:14px;">Usa el código</p>
                            <p style="margin:0 0 6px; font-size:24px; font-weight:bold; letter-spacing:1px;">EJEMPLO10</p>
                            <p style="margin:0; font-size:14px;">10% de descuento en esta categoría</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>';

        // Datos de ejemplo también para los namespaces {{contact.*}},
        // {{quote.*}} y {{cart.*}} (ver App\Support\EmailTemplateRenderer).
        // Sin esto, la vista previa del editor mostraba esos marcadores sin
        // sustituir, aunque en el envío real sí se llenan (campañas y
        // secuencias) — el editor ofrece esos tokens como chips, así que la
        // vista previa tiene que poder dibujarlos.
        //
        // La cotización se arma en memoria y NUNCA se guarda: dummyPlaceholderData()
        // es estática y no debe tocar la base de datos. created_at se fija a
        // mano porque {{quote.valid_until}} se calcula a partir de ella.
        $quote = new Cotizacion([
            'folio' => 'COT-2026-04500',
            'total' => 12500.00,
            'currency' => 'MXN',
        ]);
        $quote->created_at = now();

        return [
            'nombre_cliente' => 'Cliente Ejemplo',
            'categoria' => 'Laptops',
            'productos' => $productos,
            'cupon_codigo' => 'EJEMPLO10',
            'cupon_descuento' => '10%',
            'cupon_bloque' => $cuponBloque,
            'contact' => [
                'name' => 'Cliente Ejemplo',
                'email' => 'cliente@ejemplo.com',
                'company' => 'Empresa Ejemplo',
            ],
            'quote' => $quote,
            'cart' => [
                'total' => 15398.00,
                'items' => [
                    ['name' => 'Laptop Ejemplo 14"', 'price' => 14999.00, 'qty' => 1, 'line_total' => 14999.00],
                    ['name' => 'Mouse inalámbrico Ejemplo', 'price' => 399.00, 'qty' => 1, 'line_total' => 399.00],
                ],
            ],
        ];
    }
}
