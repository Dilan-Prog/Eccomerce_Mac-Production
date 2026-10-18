<?php

namespace App\Support\Attribution;

use App\Models\TrackConversion;

/**
 * Extrae el folio de contacto (MN + 5 caracteres) de un texto, como lo haria n8n y como lo haria un lector
 * mas tolerante. Sirve al laboratorio de atribucion para avisar cuando SOLO la lectura tolerante ve el
 * folio (es decir, cuando la regex literal de n8n lo perderia).
 *
 * Tiene un espejo en JS (public/admin-ui/js/attribution-lab/lab.js); ambos se prueban con los mismos casos
 * de tests/fixtures/folio-extraction.json. Por eso la regex tolerante usa lookarounds en lugar de \b y se
 * aplica SIN el modificador /u (paridad exacta con el motor de JS).
 */
class FolioExtractor
{
    /** Alfabeto del folio (sin vocales ni caracteres ambiguos). Igual que TrackConversion::FOLIO_REGEX. */
    private const ALPHABET = '2-9BCDFGHJKMNPQRSTVWXYZ';

    /**
     * Regex literal de n8n: `\bMN[…]{5}\b`, sensible a mayusculas, sobre el texto CRUDO. No tolera guion,
     * espacio, minusculas ni invisibles intercalados.
     */
    private const N8N_REGEX = '/\bMN[' . self::ALPHABET . ']{5}\b/';

    /**
     * Regex tolerante, sobre el texto ya normalizado: acepta `MN-7K4Q2` y `MN 7K4Q2`. Exige que no haya
     * letra ni digito pegados por ningun lado (los lookarounds sustituyen a \b: con \b, `_MN7K4Q2` fallaria).
     */
    private const TOLERANT_REGEX = '/(?<![A-Za-z0-9])MN[-\s]?([' . self::ALPHABET . ']{5})(?![A-Za-z0-9])/';

    /** Caracteres invisibles que se ignoran: U+200B, U+200C, U+200D, U+2060 y U+FEFF. */
    private const INVISIBLES = '/[\x{200B}\x{200C}\x{200D}\x{2060}\x{FEFF}]/u';

    /** Folio invisible: U+2060 + bits (U+200B = 0, U+200C = 1) + U+2060. Ver encodeInvisible en mdn-contact.js. */
    private const INVISIBLE_FOLIO = '/\x{2060}([\x{200B}\x{200C}]*)\x{2060}/u';

    /**
     * Mayusculas, sin caracteres invisibles (U+200B/200C/200D/2060/FEFF) y con el espacio duro (U+00A0)
     * convertido en espacio normal. NO colapsa espacios: `MN  7K4Q2` (dos espacios) sigue sin coincidir.
     */
    public static function normalize(string $text): string
    {
        $text = mb_strtoupper($text, 'UTF-8');
        $text = preg_replace(self::INVISIBLES, '', $text) ?? $text;

        return str_replace("\u{00A0}", ' ', $text);
    }

    /**
     * @return array{exact_n8n: ?string, tolerant: ?string, source: ?string, all: string[]}
     *   - exact_n8n: primer folio que ve la regex literal de n8n sobre el texto crudo.
     *   - tolerant: ultimo folio visible (`MN`+5, sin guion ni espacio) o, si no hay visible, el folio
     *     invisible decodificado. Gana el ULTIMO porque el cliente puede citar un mensaje anterior.
     *   - source: 'visible' | 'invisible' | null.
     *   - all: todos los folios visibles en orden (o el invisible, si es el unico).
     */
    public static function extract(string $text): array
    {
        $exact = preg_match(self::N8N_REGEX, $text, $m) === 1 ? $m[0] : null;

        $all = [];
        if (preg_match_all(self::TOLERANT_REGEX, self::normalize($text), $matches) > 0) {
            $all = array_map(fn (string $code) => 'MN' . $code, $matches[1]);
        }

        if ($all !== []) {
            return ['exact_n8n' => $exact, 'tolerant' => end($all), 'source' => 'visible', 'all' => $all];
        }

        $invisible = self::decodeInvisible($text);
        if ($invisible !== null) {
            return ['exact_n8n' => $exact, 'tolerant' => $invisible, 'source' => 'invisible', 'all' => [$invisible]];
        }

        return ['exact_n8n' => $exact, 'tolerant' => null, 'source' => null, 'all' => []];
    }

    /**
     * Decodifica el folio invisible del texto ORIGINAL (8 bits por caracter, como encodeInvisible).
     * Devuelve el primer bloque U+2060…U+2060 que decodifica a un folio valido, o null.
     */
    private static function decodeInvisible(string $text): ?string
    {
        if (preg_match_all(self::INVISIBLE_FOLIO, $text, $blocks) < 1) {
            return null;
        }

        foreach ($blocks[1] as $block) {
            // Cada bit es un caracter de 3 bytes en UTF-8; se mapea a '0'/'1' y se agrupa de 8 en 8.
            $bits = str_replace(["\u{200B}", "\u{200C}"], ['0', '1'], $block);
            $decoded = '';
            for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
                $decoded .= chr((int) bindec(substr($bits, $i, 8)));
            }
            // strlen: FOLIO_REGEX termina en `$`, que tambien acepta un salto de linea final.
            if (strlen($decoded) === 7 && preg_match(TrackConversion::FOLIO_REGEX, $decoded) === 1) {
                return $decoded;
            }
        }

        return null;
    }
}
