<?php

namespace App\Support\Attribution;

use App\Models\AttributionLabSetting;
use App\Models\User;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

/**
 * Modo prueba del Laboratorio de atribucion.
 *
 * La cookie (cifrada por EncryptCookies, HttpOnly) la emite SOLO el panel admin a un usuario con
 * el modulo 'site'. Lleva {u: id de usuario, e: expiracion (unix), n: nonce}. Se valida en cada uso:
 * firma/cifrado (EncryptCookies), expiracion y que el usuario siga siendo admin con acceso a 'site'.
 * No hay middleware global: las vistas y TrackConversionController llaman a active().
 */
class AttributionTestMode
{
    private const ATTR = 'attribution_test_mode';

    /** Datos validos de la cookie de prueba ({u,e,n}) o null. Memoizado por request. */
    public static function payload(?Request $request = null): ?array
    {
        $request ??= request();
        if ($request->attributes->has(self::ATTR)) {
            return $request->attributes->get(self::ATTR) ?: null;
        }

        $result = null;
        $raw = self::readCookie($request);
        if ($raw !== null) {
            $data = json_decode($raw, true);
            if (is_array($data)
                && isset($data['u'], $data['e'], $data['n'], $data['s'])
                && is_string($data['s'])
                && hash_equals(self::sign((int) $data['u'], (int) $data['e'], (string) $data['n']), $data['s'])
                && is_numeric($data['u']) && is_numeric($data['e'])
                && is_string($data['n']) && $data['n'] !== ''
                && (int) $data['e'] > time()) {
                $user = User::query()->find((int) $data['u']);
                if ($user && $user->role === 'admin' && $user->canAccessModule('site')) {
                    $result = ['u' => (int) $data['u'], 'e' => (int) $data['e'], 'n' => $data['n']];
                }
            }
        }

        $request->attributes->set(self::ATTR, $result ?? false);

        return $result;
    }

    /**
     * Valor de la cookie de prueba en claro. En el grupo `web` EncryptCookies ya la descifro; en
     * las URL sin ruta (404) ese middleware no corre y llega cifrada: se descifra a mano. Un valor
     * sin cifrar solo es aceptable si trae la firma HMAC (ver sign()), que no se puede falsificar
     * sin APP_KEY.
     */
    private static function readCookie(Request $request): ?string
    {
        $raw = $request->cookies->get(self::cookieName());
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        if (str_starts_with(ltrim($raw), '{')) {
            return $raw;
        }
        try {
            $encrypter = app('encrypter');
            $plain = $encrypter->decrypt($raw, false);
            $value = CookieValuePrefix::validate(self::cookieName(), $plain, $encrypter->getKey());

            return is_string($value) && $value !== '' ? $value : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Firma HMAC del contenido de la cookie (ligada a APP_KEY). */
    private static function sign(int $u, int $e, string $n): string
    {
        return hash_hmac('sha256', $u . '|' . $e . '|' . $n, (string) config('app.key'));
    }

    /** JSON firmado de la cookie de prueba: {u, e, n, s}. */
    public static function encodePayload(int $u, int $e, string $n): string
    {
        return json_encode(['u' => $u, 'e' => $e, 'n' => $n, 's' => self::sign($u, $e, $n)]);
    }

    public static function active(?Request $request = null): bool
    {
        return self::payload($request) !== null;
    }

    public static function cookieName(): string
    {
        return (string) config('attribution.test_mode.cookie', 'mdn_attr_test');
    }

    /** Cookie para activar el modo prueba (se encola con Cookie::queue o ->withCookie). */
    public static function cookie(Request $request, User $user): SymfonyCookie
    {
        $ttl = max(1, (int) config('attribution.test_mode.ttl_minutes', 120));
        $value = self::encodePayload((int) $user->id, time() + $ttl * 60, Str::random(16));

        return cookie(
            self::cookieName(), $value, $ttl, '/', self::cookieDomain($request->getHost()),
            self::isSecure($request), true, false, 'lax'
        );
    }

    /** Cookie que expira la de prueba (mismo nombre, ruta y dominio). */
    public static function forgetCookie(Request $request): SymfonyCookie
    {
        return cookie()->forget(self::cookieName(), '/', self::cookieDomain($request->getHost()));
    }

    /** Mismo criterio que cookieDomainFor() de attribution.js. */
    public static function cookieDomain(string $host): ?string
    {
        $h = strtolower(trim($host));
        if ($h === 'macdelnorte.com' || str_ends_with($h, '.macdelnorte.com')) {
            return '.macdelnorte.com';
        }

        return null;
    }

    private static function isSecure(Request $request): bool
    {
        if ($request->isSecure()) {
            return true;
        }

        return app()->environment('production') && str_starts_with((string) config('app.url'), 'https://');
    }

    /**
     * Fuente y valor crudo del numero de prueba: el guardado en la BD desde el panel (clave
     * test_whatsapp_number) manda sobre el .env; solo si la BD no tiene nada se usa el .env.
     * Un valor de BD no vacio pero invalido NO cae al .env (se trata como invalido).
     *
     * @return array{0:'db'|'env'|'none', 1:string}
     */
    private static function resolveNumber(): array
    {
        $db = trim((string) AttributionLabSetting::get(AttributionLabSetting::KEY_TEST_WHATSAPP, ''));
        if ($db !== '') {
            return ['db', $db];
        }

        $env = trim((string) config('attribution.test_mode.whatsapp_number'));

        return $env !== '' ? ['env', $env] : ['none', ''];
    }

    /** Normaliza a solo digitos y valida 10-15; '' si no es valido. */
    private static function validDigits(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);

        return ($digits !== '' && strlen($digits) >= 10 && strlen($digits) <= 15) ? $digits : '';
    }

    /** Numero de prueba valido (solo digitos, 10-15) o '' si no esta configurado / es invalido. */
    public static function configuredNumber(): string
    {
        return self::validDigits(self::resolveNumber()[1]);
    }

    /** True si hay algo configurado (BD o .env) pero no es un numero valido. */
    public static function configuredNumberInvalid(): bool
    {
        [, $raw] = self::resolveNumber();

        return $raw !== '' && self::validDigits($raw) === '';
    }

    /** De donde sale el numero de prueba: 'db' (panel), 'env' (.env) o 'none' (nada configurado). */
    public static function configuredNumberSource(): string
    {
        return self::resolveNumber()[0];
    }

    /**
     * Numero de WhatsApp que deben abrir los enlaces impresos desde Blade: en modo prueba el de
     * prueba ('' = wa.me/?text= sin numero); fuera de modo prueba, el real (contact.whatsapp.number).
     */
    public static function whatsappNumber(?Request $request = null): string
    {
        if (self::active($request)) {
            return self::configuredNumber();
        }

        return (string) config('contact.whatsapp.number');
    }
}
