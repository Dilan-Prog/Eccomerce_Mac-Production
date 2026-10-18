<?php

namespace Tests\Unit;

use App\Models\TrackConversion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TrackConversionCanonicalTypeTest extends TestCase
{
    public static function typeMap(): array
    {
        return [
            // WhatsApp: todo lo que empieza con "whatsapp" (guion o guion bajo, MX y BR)
            ['whatsapp_click', 'whatsapp_click'],
            ['whatsapp', 'whatsapp_click'],
            ['whatsapp_footer_oficina', 'whatsapp_click'],
            ['whatsapp_menu_cotizacion', 'whatsapp_click'],
            ['whatsapp_servicios', 'whatsapp_click'],
            ['whatsapp-flotante', 'whatsapp_click'],
            ['whatsapp_flotante', 'whatsapp_click'],
            ['whatsapp_br_dc1010_hero', 'whatsapp_click'],
            // Telefono
            ['phone_click', 'phone_click'],
            ['telefono', 'phone_click'],
            ['telefono_contactos', 'phone_click'],
            ['telefono_servicios', 'phone_click'],
            // Correo
            ['email_click', 'email_click'],
            ['correo_footer_ventas', 'email_click'],
            ['correo', 'email_click'],
            // Tipos de otra familia: se respetan
            ['page_visit', 'page_visit'],
            ['quote_submit', 'quote_submit'],
            ['quote_start', 'quote_start'],
            ['quote_dismiss', 'quote_dismiss'],
            ['add_to_cart', 'add_to_cart'],
            ['add_to_cart_blocked', 'add_to_cart_blocked'],
            ['job_apply', 'job_apply'],
            ['job_question', 'job_question'],
            // Desconocido: tal cual
            ['algo_nuevo', 'algo_nuevo'],
            ['Contacto', 'Contacto'],
        ];
    }

    #[DataProvider('typeMap')]
    public function test_canonical_type_by_prefix(string $input, string $expected): void
    {
        $this->assertSame($expected, TrackConversion::canonicalType($input));
    }

    public function test_null_stays_null(): void
    {
        $this->assertNull(TrackConversion::canonicalType(null));
    }

    public function test_click_id_format_rules(): void
    {
        $this->assertTrue(TrackConversion::isValidGclid('TEST_GCLID_0123456789ABCDEF'));
        $this->assertFalse(TrackConversion::isValidGclid('TEST123'));
        $this->assertFalse(TrackConversion::isValidGclid('gtm_abcdefghijklmnopqrstuvwxyz'));
        $this->assertFalse(TrackConversion::isValidGclid('12345678901234567890'));
        $this->assertFalse(TrackConversion::isValidGclid(''));
        $this->assertFalse(TrackConversion::isValidGclid(null));
        $this->assertFalse(TrackConversion::isValidGclid(str_repeat('a', 256)));
        $this->assertTrue(TrackConversion::isValidBraid('0AbCdEfGhIjK'));
        $this->assertFalse(TrackConversion::isValidBraid('short'));
        $this->assertFalse(TrackConversion::isValidBraid('has space in it 123'));
    }
}
