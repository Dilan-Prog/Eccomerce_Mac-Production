<?php

namespace App\Support;

use App\Models\Cotizacion;
use App\Models\EmailTemplate;
use App\Models\User;

/**
 * Motor de reemplazo de placeholders para las plantillas de correo editables
 * desde el admin (ver App\Http\Controllers\Backend\EmailTemplateController y
 * app/Models/EmailTemplate.php).
 *
 * Placeholders "planos" (compatibilidad total con lo ya construido para
 * MarketingOfferBuilder — nunca cambian de comportamiento):
 * - {{nombre_cliente}}   nombre del cliente (texto, escapado)
 * - {{categoria}}        nombre de la categoría dominante (texto, escapado)
 * - {{productos}}        bloque HTML de tarjetas de producto (ya HTML, sin escapar)
 * - {{cupon_codigo}}     código del cupón, vacío si no hay (texto, escapado)
 * - {{cupon_descuento}}  texto del descuento ("10%" / "$100 MXN"), vacío si no hay (texto, escapado)
 * - {{cupon_bloque}}     sección de cupón completa, ya armada condicionalmente (ya HTML, sin escapar)
 *
 * Placeholders de "namespace" (sistema nuevo, generalizado para servir a
 * cualquier tipo de correo del sitio — marketing, cotizaciones, recuperación
 * de carrito — no solo ofertas). Cada namespace solo se rellena si su objeto
 * correspondiente viene en $data; si no viene, sus marcadores se quedan tal
 * cual en el resultado — NUNCA truena, NUNCA los borra. Esta es la misma
 * regla que ya cumplían los placeholders planos de arriba cuando no había
 * cupón/categoría, solo que ahora aplica por namespace completo:
 *
 * - {{contact.name}}     $data['contact'] instancia de App\Models\User
 * - {{contact.email}}
 * - {{contact.company}}
 * - {{quote.quote_number}}  $data['quote'] instancia de App\Models\Cotizacion (= folio)
 * - {{quote.total}}
 * - {{quote.currency}}
 * - {{quote.valid_until}}   created_at + 15 días (igual que resources/views/cotizaciones/pdf.blade.php)
 *   {{quote.notes}} NO tiene fuente real en el modelo Cotizacion (no hay
 *   columna de notas) — deliberadamente no se agrega al mapa de reemplazo,
 *   así se queda sin sustituir, como cualquier otro marcador sin dato.
 * - {{cart.total}}       $data['cart'] = ['total' => float, 'items' => [...]] (formateado como moneda)
 * - {{cart.items_table}} tabla HTML de líneas del carrito (mismo patrón de tabla con estilos en línea que MarketingOfferBuilder::renderProductsBlock())
 *   {{cart.recovery_url}} NUNCA se sustituye aquí — el dueño del negocio
 *   decidió explícitamente dejar la recuperación de carrito sin implementar
 *   por ahora (no existe ruta pública para eso todavía). Se deja el
 *   marcador tal cual, como cualquier otro sin dato.
 * - {{deal.*}}           no existe ningún concepto de "deal" en este
 *   proyecto todavía — nunca se sustituye, no hay lógica real, solo este
 *   comentario documentando el porqué.
 *
 * $data (ver MarketingOfferBuilder::placeholderData() para los planos, y los
 * comentarios de arriba para los namespaces) trae los valores ya resueltos;
 * este renderer solo hace la sustitución str_replace.
 *
 * IMPORTANTE — render() es una función "pura": misma plantilla + mismos
 * datos de entrada = mismo resultado, siempre. A propósito NUNCA resuelve
 * aquí:
 * - {{unsubscribe_url}} ni el píxel de apertura de correo: eso pertenece a
 *   una capa posterior de envío real (todavía no construida, pendiente de
 *   contratar la plataforma de correo) que sí conoce el token de rastreo
 *   único por cada envío individual — un dato que este renderer, al ser
 *   puro, no puede ni debe inventar.
 * - Adjuntos (ej. el PDF de una cotización): nunca se generan dentro de
 *   render() — eso también es responsabilidad de la capa de envío.
 */
class EmailTemplateRenderer
{
    /** Días de vigencia de una cotización — igual que resources/views/cotizaciones/pdf.blade.php línea ~232. */
    private const QUOTE_VALIDITY_DAYS = 15;

    /**
     * @param  array{
     *     nombre_cliente?: string, categoria?: string, productos?: string,
     *     cupon_codigo?: string, cupon_descuento?: string, cupon_bloque?: string,
     *     contact?: ?User, quote?: ?Cotizacion, cart?: ?array{total: float, items: array<int, array{name?: string, image?: string, price?: float, qty?: int, line_total?: float}>},
     *     deal?: mixed
     * }  $data
     * @return array{subject: string, html: string}
     */
    public function render(EmailTemplate $template, array $data): array
    {
        [$search, $replace] = $this->buildFlatPlaceholders($data);

        [$namespaceSearch, $namespaceReplace] = $this->buildNamespacePlaceholders($data);
        $search = array_merge($search, $namespaceSearch);
        $replace = array_merge($replace, $namespaceReplace);

        return [
            'subject' => str_replace($search, $replace, $template->subject),
            'html' => str_replace($search, $replace, $template->body),
        ];
    }

    /**
     * Placeholders planos ya existentes — comportamiento intacto, sin
     * ningún cambio, para no arriesgar regresión en el flujo de ofertas de
     * marketing ya construido y probado.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function buildFlatPlaceholders(array $data): array
    {
        $search = [
            '{{nombre_cliente}}',
            '{{categoria}}',
            '{{productos}}',
            '{{cupon_codigo}}',
            '{{cupon_descuento}}',
            '{{cupon_bloque}}',
        ];

        $replace = [
            e($data['nombre_cliente'] ?? ''),
            e($data['categoria'] ?? ''),
            $data['productos'] ?? '',
            e($data['cupon_codigo'] ?? ''),
            e($data['cupon_descuento'] ?? ''),
            $data['cupon_bloque'] ?? '',
        ];

        return [$search, $replace];
    }

    /**
     * Placeholders de namespace ({{contact.*}}, {{quote.*}}, {{cart.*}}).
     * Solo agrega pares búsqueda/reemplazo para los namespaces cuyo objeto
     * viene presente y con la forma esperada en $data — cualquier otro
     * namespace (o uno cuyo objeto no vino) simplemente no aporta pares, y
     * sus marcadores se quedan tal cual en el HTML/asunto final.
     *
     * {{deal.*}} deliberadamente no tiene ninguna rama aquí: no existe el
     * concepto de "deal" en este proyecto, así que nunca hay nada que
     * sustituir para ese namespace.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function buildNamespacePlaceholders(array $data): array
    {
        $search = [];
        $replace = [];

        if (($data['contact'] ?? null) instanceof User) {
            [$s, $r] = $this->contactPlaceholders($data['contact']);
            array_push($search, ...$s);
            array_push($replace, ...$r);
        }

        if (($data['quote'] ?? null) instanceof Cotizacion) {
            [$s, $r] = $this->quotePlaceholders($data['quote']);
            array_push($search, ...$s);
            array_push($replace, ...$r);
        }

        if (is_array($data['cart'] ?? null) && !empty($data['cart'])) {
            [$s, $r] = $this->cartPlaceholders($data['cart']);
            array_push($search, ...$s);
            array_push($replace, ...$r);
        }

        return [$search, $replace];
    }

    /** @return array{0: array<int, string>, 1: array<int, string>} */
    private function contactPlaceholders(User $contact): array
    {
        // name.' '.last_name solo si last_name viene con algo — evita dejar
        // un espacio colgante al final para clientes sin apellido guardado
        // (ej. cuentas viejas o creadas por un admin sin ese dato).
        $name = trim($contact->name ?? '');
        if (!empty($contact->last_name)) {
            $name = trim($name . ' ' . $contact->last_name);
        }

        return [
            ['{{contact.name}}', '{{contact.email}}', '{{contact.company}}'],
            [e($name), e($contact->email ?? ''), e($contact->company ?? '')],
        ];
    }

    /** @return array{0: array<int, string>, 1: array<int, string>} */
    private function quotePlaceholders(Cotizacion $quote): array
    {
        // {{quote.notes}} no se incluye aquí a propósito: Cotizacion no
        // tiene columna de notas, así que ese marcador se queda sin
        // sustituir, tal cual la regla general del sistema.
        $validUntil = $quote->created_at
            ? $quote->created_at->copy()->addDays(self::QUOTE_VALIDITY_DAYS)->format('d/m/Y')
            : '';

        return [
            ['{{quote.quote_number}}', '{{quote.total}}', '{{quote.currency}}', '{{quote.valid_until}}'],
            [e($quote->folio ?? ''), e(formatCurrency($quote->total ?? 0)), e($quote->currency ?? ''), e($validUntil)],
        ];
    }

    /**
     * @param  array{total?: float, items?: array<int, array{name?: string, image?: string, price?: float, qty?: int, line_total?: float}>}  $cart
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function cartPlaceholders(array $cart): array
    {
        // {{cart.recovery_url}} deliberadamente NO está en este mapa: no
        // existe todavía una ruta pública de recuperación de carrito (el
        // dueño del negocio decidió dejarla sin implementar por ahora), así
        // que ese marcador se queda tal cual, sin sustituir.
        $total = (float) ($cart['total'] ?? 0);
        $items = is_array($cart['items'] ?? null) ? $cart['items'] : [];

        return [
            ['{{cart.total}}', '{{cart.items_table}}'],
            ['$' . formatCurrency($total) . ' MXN', $this->renderCartItemsTable($items)],
        ];
    }

    /**
     * Tabla HTML de líneas del carrito — mismo patrón de tabla con estilos
     * en línea (tarjeta con borde redondeado, imagen a la izquierda, datos a
     * la derecha) que ya usa MarketingOfferBuilder::renderProductsBlock(),
     * para no inventar un estilo nuevo de correo.
     *
     * @param  array<int, array{name?: string, image?: string, price?: float, qty?: int, line_total?: float}>  $items
     */
    private function renderCartItemsTable(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $html = '';
        foreach ($items as $item) {
            $name = (string) ($item['name'] ?? '');
            $image = !empty($item['image'])
                ? '<img src="' . e($item['image']) . '" alt="' . e($name) . '" width="90" style="display:block; width:90px; height:auto; border:0;">'
                : '';
            $qty = (int) ($item['qty'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $lineTotal = (float) ($item['line_total'] ?? ($price * $qty));

            $html .= '<tr>
                <td style="padding:0 20px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:6px;">
                        <tr>
                            <td width="110" style="padding:12px; vertical-align:top;">' . $image . '</td>
                            <td style="padding:12px 12px 12px 0; vertical-align:top;">
                                <p style="margin:0 0 6px; font-size:15px; color:#222222; font-weight:bold;">' . e($name) . '</p>
                                <p style="margin:0 0 6px; font-size:13px; color:#555555;">Cantidad: ' . $qty . ' x $' . formatCurrency($price) . ' MXN</p>
                                <p style="margin:0; font-size:16px; color:#00468c; font-weight:bold;">$' . formatCurrency($lineTotal) . ' MXN</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>';
        }

        return $html;
    }
}
