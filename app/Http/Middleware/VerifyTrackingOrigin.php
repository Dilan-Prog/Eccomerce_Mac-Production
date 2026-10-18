<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: `tracking.origin` (ver $middlewareAliases en app/Http/Kernel.php).
 * Protege POST /track-conversion, que es publico y sin CSRF: solo se guardan eventos enviados
 * desde paginas del propio sitio.
 *
 * Toma el host de la cabecera `Origin` (si no viene, del `Referer`) y lo compara, de forma EXACTA y
 * en minusculas (nunca por sufijo: `macdelnorte.com.evil.com` o `evilmacdelnorte.com` no pasan) con:
 *   - el host de config('app.url') y su variante con/sin `www.`;
 *   - los hosts extra de config('attribution.allowed_hosts') (env ATTRIBUTION_ALLOWED_HOSTS, por comas).
 * Las paginas /br/* viven en el mismo dominio, asi que quedan cubiertas por el mismo host.
 * Solo en entornos `local` y `testing` ademas se permiten localhost, 127.0.0.1, ::1 y los hosts
 * `*.test` / `*.localhost` (Laragon), y se ignora el puerto; en el resto, un puerto distinto de
 * 80/443 no coincide.
 *
 * Si no hay Origin ni Referer, o el host no coincide, responde 204 No Content SIN guardar nada y
 * sin error visible (un bot o un curl no aprende nada). Para depurar: un 204 significa "no
 * guardado". Interruptor: config('attribution.origin_check') (env ATTRIBUTION_ORIGIN_CHECK, true
 * por defecto); en false deja pasar todo, para apagarlo sin redeploy si algo falla.
 */
class VerifyTrackingOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('attribution.origin_check', true)) {
            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        return response()->noContent();
    }

    private function isAllowed(Request $request): bool
    {
        $source = (string) ($request->headers->get('Origin') ?: $request->headers->get('Referer'));
        if ($source === '') {
            return false;
        }

        $parts = parse_url($source);
        $host = is_array($parts) && isset($parts['host']) ? strtolower(trim($parts['host'], '[]')) : '';
        if ($host === '') {
            return false; // p. ej. Origin: null
        }

        $isDev = app()->environment(['local', 'testing']);

        if (!$isDev && isset($parts['port']) && !in_array((int) $parts['port'], [80, 443], true)) {
            return false;
        }

        if (in_array($host, $this->allowedHosts(), true)) {
            return true;
        }

        return $isDev && (
            in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost')
        );
    }

    /** @return array<int,string> Hosts permitidos (minusculas, sin duplicados). */
    private function allowedHosts(): array
    {
        $hosts = [];

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (is_string($appHost) && $appHost !== '') {
            $appHost = strtolower($appHost);
            $hosts[] = $appHost;
            $hosts[] = str_starts_with($appHost, 'www.') ? substr($appHost, 4) : 'www.' . $appHost;
        }

        $extra = config('attribution.allowed_hosts', []);
        if (is_string($extra)) {
            $extra = explode(',', $extra);
        }
        foreach ((array) $extra as $host) {
            $host = strtolower(trim((string) $host));
            if ($host !== '') {
                $hosts[] = $host;
            }
        }

        return array_values(array_unique($hosts));
    }
}
