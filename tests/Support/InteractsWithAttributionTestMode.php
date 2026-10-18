<?php

namespace Tests\Support;

use App\Models\User;
use App\Support\Attribution\AttributionTestMode;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Support\Facades\Schema;

/**
 * Ayudas para pruebas del modo prueba de atribucion (cookie cifrada) y de usuarios admin.
 * Usar junto con CreatesTrackConversionsTable (BD SQLite en memoria, sin RefreshDatabase).
 */
trait InteractsWithAttributionTestMode
{
    /** Crea SOLO las tablas de usuarios/roles que necesita el modo prueba (idempotente). */
    protected function prepareAdminUsersTables(): void
    {
        if (Schema::hasTable('users') && Schema::hasTable('roles') && Schema::hasColumn('users', 'role_id')) {
            return;
        }
        foreach ([
            '2014_10_12_000000_create_users_table.php',
            '2026_08_02_110000_create_roles_table.php',
            '2026_08_02_110001_create_role_module_permissions_table.php',
            '2026_08_02_110002_add_role_id_and_is_protected_to_users_table.php',
        ] as $file) {
            $path = database_path('migrations/' . $file);
            $migration = require $path;
            try {
                $migration->up();
            } catch (\Throwable $e) {
                // tablas ya creadas o migraciones solo-MySQL: se ignora, el test fallara si falta algo real
            }
        }
    }

    /** Valor ya cifrado de la cookie de prueba, tal como lo emitiria el servidor. */
    protected function testModeCookieValue(User $user, ?int $expiresAt = null, string $nonce = 'nonce-test-1234'): string
    {
        $name = AttributionTestMode::cookieName();
        $json = AttributionTestMode::encodePayload((int) $user->id, $expiresAt ?? (time() + 3600), $nonce);

        return app('encrypter')->encrypt(CookieValuePrefix::create($name, app('encrypter')->getKey()) . $json, false);
    }

    /** Equivale a navegar con el modo prueba activo como $user: $this->withTestModeCookie($u)->post(...). */
    protected function withTestModeCookie(User $user, ?int $expiresAt = null, string $nonce = 'nonce-test-1234'): static
    {
        // El valor ya va cifrado: withCookie() lo cifraria de nuevo y postJson() no manda cookies sin withCredentials().
        return $this->withCredentials()->withUnencryptedCookie(AttributionTestMode::cookieName(), $this->testModeCookieValue($user, $expiresAt, $nonce));
    }
}
