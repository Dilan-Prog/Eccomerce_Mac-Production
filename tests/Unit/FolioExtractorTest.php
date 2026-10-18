<?php

namespace Tests\Unit;

use App\Support\Attribution\FolioExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Casos compartidos con la prueba JS del laboratorio (tests/fixtures/folio-extraction.json): ambos lados
 * deben dar exactamente lo mismo.
 *
 * Dos lecturas del mismo texto:
 *  - exact_n8n: la regex LITERAL de n8n, `\bMN[2-9BCDFGHJKMNPQRSTVWXYZ]{5}\b`, sensible a mayusculas, sobre
 *    el texto crudo.
 *  - tolerant: la lectura recomendada (mayusculas, ignora invisibles, acepta `MN-7K4Q2` y `MN 7K4Q2`).
 *
 * Casos que "tolerant" extrae pero "exact_n8n" NO (n8n perderia el folio si no se ajusta su regex):
 *  - con guion o espacio entre MN y el codigo (`MN-7K4Q2`, `MN 7K4Q2`, salto de linea, espacio duro U+00A0);
 *  - en minusculas o mezcladas (`mn7k4q2`, `Mn7k4Q2`);
 *  - con invisibles intercalados (U+200B/200C/200D/2060/FEFF entre los caracteres del folio);
 *  - pegado a un guion bajo (`ref_MN7K4Q2`: el guion bajo es "palabra" y \b no corta);
 *  - el folio invisible (solo se decodifica del texto original; n8n no lo ve).
 * Con dos o mas folios exact_n8n toma el PRIMERO que coincida y tolerant el ULTIMO (el cliente puede citar
 * un mensaje anterior).
 *
 * Falso positivo conocido de la lectura tolerante: "100 MN 23456" (MN = moneda nacional) se lee como folio.
 */
class FolioExtractorTest extends TestCase
{
    /** @return array<string,array{0:string,1:?string,2:?string,3:?string}> */
    public static function cases(): array
    {
        $path = dirname(__DIR__) . '/fixtures/folio-extraction.json';
        $cases = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $out = [];
        foreach ($cases as $case) {
            $out[$case['name']] = [$case['text'], $case['exact_n8n'], $case['tolerant'], $case['source']];
        }

        return $out;
    }

    #[DataProvider('cases')]
    public function test_extract_matches_the_shared_fixture(string $text, ?string $exact, ?string $tolerant, ?string $source): void
    {
        $r = FolioExtractor::extract($text);

        $this->assertSame($exact, $r['exact_n8n'], 'exact_n8n');
        $this->assertSame($tolerant, $r['tolerant'], 'tolerant');
        $this->assertSame($source, $r['source'], 'source');
    }

    public function test_the_fixture_is_well_formed(): void
    {
        $cases = json_decode((string) file_get_contents(dirname(__DIR__) . '/fixtures/folio-extraction.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertGreaterThanOrEqual(30, count($cases));
        $names = array_column($cases, 'name');
        $this->assertSame($names, array_unique($names), 'Los nombres de caso son unicos');
        foreach ($cases as $case) {
            $this->assertSame(['name', 'text', 'exact_n8n', 'tolerant', 'source'], array_keys($case));
            $this->assertContains($case['source'], ['visible', 'invisible', null]);
            $this->assertSame($case['tolerant'] === null, $case['source'] === null, $case['name']);
        }
    }

    public function test_all_lists_every_visible_folio_in_order_and_tolerant_is_the_last(): void
    {
        $r = FolioExtractor::extract('Primero MN7K4Q2, luego mn-3fgh8 y al final MN 5CD6F');

        $this->assertSame(['MN7K4Q2', 'MN3FGH8', 'MN5CD6F'], $r['all']);
        $this->assertSame('MN5CD6F', $r['tolerant']);
        $this->assertSame('MN7K4Q2', $r['exact_n8n']);
        $this->assertSame('visible', $r['source']);
    }

    public function test_all_is_empty_when_nothing_is_found(): void
    {
        $this->assertSame(
            ['exact_n8n' => null, 'tolerant' => null, 'source' => null, 'all' => []],
            FolioExtractor::extract('Hola, quiero cotizar')
        );
    }

    public function test_extract_returns_exactly_the_documented_keys(): void
    {
        $this->assertSame(['exact_n8n', 'tolerant', 'source', 'all'], array_keys(FolioExtractor::extract('MN7K4Q2')));
    }

    public function test_normalize_uppercases_strips_invisibles_and_turns_nbsp_into_a_plain_space(): void
    {
        $this->assertSame('MN7K4Q2', FolioExtractor::normalize("m\u{200B}n\u{200C}7\u{200D}k\u{2060}4\u{FEFF}q2"));
        $this->assertSame('MN 7K4Q2', FolioExtractor::normalize("mn\u{00A0}7k4q2"));
        $this->assertSame('MN  7K4Q2', FolioExtractor::normalize('mn  7k4q2'), 'No colapsa espacios');
        $this->assertSame('', FolioExtractor::normalize(''));
        // Letras acentuadas se pasan a mayusculas sin romper la codificacion.
        $this->assertSame('OLÁ', FolioExtractor::normalize('olá'));
    }
}
