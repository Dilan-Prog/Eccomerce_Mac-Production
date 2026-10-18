<?php

namespace Tests\Support;

use App\Models\TrackConversion;
use Illuminate\Support\Facades\DB;

/**
 * Prepara SOLO la tabla track_conversions en la SQLite en memoria de `php artisan test`.
 *
 * No usa RefreshDatabase: el resto de migraciones del proyecto son de MySQL y no corren en SQLite.
 * Cada migracion de la tabla se ejecuta a mano con `(require $path)->up()`.
 */
trait CreatesTrackConversionsTable
{
    /** Origin del sitio en las pruebas (coincide con el app.url que fija prepareTrackConversionsTable). */
    protected const TRACK_ORIGIN = 'https://macdelnorte.com';

    protected function prepareTrackConversionsTable(): void
    {
        // POST /track-conversion solo guarda eventos con Origin/Referer del propio sitio (tracking.origin).
        config(['app.url' => 'https://macdelnorte.com', 'attribution.origin_check' => true, 'attribution.allowed_hosts' => []]);

        // Con config:cache el phpunit.xml no podria cambiar la conexion y estas pruebas tocarian la
        // BD real. Se aborta antes de crear nada.
        $this->assertFileDoesNotExist(
            base_path('bootstrap/cache/config.php'),
            'Existe bootstrap/cache/config.php: ejecuta `php artisan config:clear` antes de correr las pruebas.'
        );
        $this->assertSame('sqlite', DB::getDefaultConnection(), 'Las pruebas deben usar SQLite en memoria.');
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        foreach ([
            '2025_07_08_162704_create_track_conversions_table.php',
            '2026_10_08_120000_add_attribution_columns_to_track_conversions_table.php',
            '2026_10_08_130000_add_meta_to_track_conversions_table.php',
            '2026_10_19_100000_add_contact_folio_and_event_at_to_track_conversions_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }
    }

    /**
     * Inserta un evento. $createdAt (UTC, 'Y-m-d H:i:s') fija created_at para controlar el orden y las ventanas.
     */
    protected function makeEvent(array $attributes = [], ?string $createdAt = null): TrackConversion
    {
        $event = new TrackConversion();
        $event->forceFill(array_merge(['type' => 'whatsapp_click'], $attributes));
        if ($createdAt) {
            $event->created_at = $createdAt;
            $event->updated_at = $createdAt;
        }
        $event->save();

        return $event;
    }

    /** POST JSON a /track-conversion con el Origin correcto del sitio (sobrescribible con $headers). */
    protected function postTrack(array $payload, array $headers = [])
    {
        return $this->postJson('/track-conversion', $payload, array_merge(['Origin' => self::TRACK_ORIGIN], $headers));
    }
}
