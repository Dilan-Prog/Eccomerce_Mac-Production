<?php

namespace App\Support\WebLeads;

use App\Mail\WebLeadReceivedMail;
use App\Models\WebLead;
use App\Models\WebLeadSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Avisa de una solicitud web (web_leads) por correo y por webhook opcional (n8n).
 *
 * Cada canal es independiente: si uno falla se guarda el error en la solicitud y el otro se intenta igual.
 * Tambien lo usa el panel admin para reenviar:
 *     app(\App\Support\WebLeads\WebLeadNotifier::class)->notify($lead);
 *
 * Nunca se loguea el token ni el cuerpo (datos personales).
 */
class WebLeadNotifier
{
    public function notify(WebLead $lead): void
    {
        $this->notifyByMail($lead);
        $this->notifyByWebhook($lead);
    }

    /** Maximo de destinatarios del aviso. */
    public const MAX_RECIPIENTS = 10;

    /**
     * Texto con correos (separados por coma, punto y coma, espacio o salto de linea) => [validos unicos, invalidos].
     *
     * @return array{0: list<string>, 1: list<string>}
     */
    public static function parseEmails(string $raw): array
    {
        $valid = [];
        $invalid = [];
        foreach (preg_split('/[\s,;]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $candidate) {
            $email = strtolower($candidate);
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $invalid[] = $candidate;
            } elseif (!in_array($email, $valid, true)) {
                $valid[] = $email;
            }
        }

        return [$valid, $invalid];
    }

    /**
     * Quien recibe el aviso: lo guardado en el panel tiene prioridad; si no hay nada, WEB_LEADS_NOTIFY_TO del
     * .env (config/webleads.php); si tampoco, nadie (la solicitud igual queda guardada).
     *
     * @return list<string>
     */
    public static function recipients(): array
    {
        $fromPanel = WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO);
        if ($fromPanel !== null) {
            return self::parseEmails($fromPanel)[0];
        }

        return array_values(array_filter((array) config('webleads.notify_to', [])));
    }

    /** De donde salen los destinatarios actuales: 'panel' | 'env' | 'none'. */
    public static function recipientsSource(): string
    {
        if (WebLeadSetting::get(WebLeadSetting::KEY_NOTIFY_TO) !== null) {
            return 'panel';
        }

        return array_filter((array) config('webleads.notify_to', [])) !== [] ? 'env' : 'none';
    }

    private function notifyByMail(WebLead $lead): void
    {
        $to = self::recipients();
        if ($to === []) {
            return; // sin destinos: solo queda guardada y visible en el panel
        }

        try {
            Mail::to($to)->send(new WebLeadReceivedMail($lead));
            $lead->update(['notified_at' => now(), 'notify_error' => null]);
        } catch (\Throwable $e) {
            $lead->update(['notify_error' => $this->shortError($e->getMessage())]);
            Log::warning('WebLead: no se pudo enviar el correo', [
                'web_lead_id' => $lead->id,
                'exception' => get_class($e),
            ]);
        }
    }

    private function notifyByWebhook(WebLead $lead): void
    {
        $url = trim((string) config('webleads.webhook_url', ''));
        if ($url === '') {
            return;
        }

        try {
            $problem = $this->urlProblem($url);
            if ($problem !== null) {
                $lead->update(['webhook_error' => $problem]);
                Log::warning('WebLead: URL de webhook no permitida', ['web_lead_id' => $lead->id, 'motivo' => $problem]);

                return;
            }

            $request = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('webleads.webhook_timeout', 10))
                ->withOptions(['allow_redirects' => false]);

            $token = trim((string) config('webleads.webhook_token', ''));
            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $response = $request->post($url, $this->payload($lead));

            if ($response->successful()) {
                $lead->update(['webhook_sent_at' => now(), 'webhook_error' => null]);

                return;
            }

            $lead->update(['webhook_error' => 'El webhook respondió HTTP ' . $response->status()]);
            Log::warning('WebLead: el webhook respondió con error', [
                'web_lead_id' => $lead->id,
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            // El mensaje de Guzzle puede incluir la URL (con token en la ruta): se quita.
            $lead->update(['webhook_error' => $this->shortError(str_replace($url, '[url]', $e->getMessage()))]);
            Log::warning('WebLead: no se pudo llamar al webhook', [
                'web_lead_id' => $lead->id,
                'exception' => get_class($e),
            ]);
        }
    }

    /** Cuerpo JSON del webhook. */
    public function payload(WebLead $lead): array
    {
        return [
            'id' => $lead->id,
            'form_type' => $lead->form_type,
            'form_label' => $lead->form_label,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'full_name' => $lead->full_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'city' => $lead->city,
            'state' => $lead->state,
            'operation' => $lead->operation,
            'message' => $lead->message,
            'source_page' => $lead->source_page,
            'landing_page' => $lead->landing_page,
            'referrer' => $lead->referrer,
            'gclid' => $lead->gclid,
            'gbraid' => $lead->gbraid,
            'wbraid' => $lead->wbraid,
            'fbclid' => $lead->fbclid,
            'utm_source' => $lead->utm_source,
            'utm_medium' => $lead->utm_medium,
            'utm_campaign' => $lead->utm_campaign,
            'utm_term' => $lead->utm_term,
            'utm_content' => $lead->utm_content,
            'created_at' => $lead->created_at?->toIso8601String(),
        ];
    }

    /**
     * SSRF basico: solo http(s), sin credenciales en la URL y, fuera de local/testing, solo hosts que
     * resuelvan a IPs publicas. Devuelve el motivo del rechazo o null si es valida.
     * (Limite conocido: la IP se valida antes de la llamada; un DNS-rebinding en esa ventana no se cubre.)
     */
    private function urlProblem(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return 'La URL del webhook no es válida.';
        }
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return 'La URL del webhook debe ser http o https.';
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            return 'La URL del webhook no puede llevar usuario ni contraseña.';
        }

        if (app()->environment('local', 'testing')) {
            return null; // en desarrollo se permite apuntar a n8n local
        }

        $host = strtolower(trim($parts['host'], '[]'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return 'La URL del webhook apunta a un servidor interno.';
        }

        $ips = $this->resolveHostIps($host);
        if ($ips === []) {
            return 'No se pudo resolver el servidor del webhook.';
        }
        foreach ($ips as $ip) {
            if (!$this->isPublicIp($ip)) {
                return 'La URL del webhook apunta a una red privada o reservada.';
            }
        }

        return null;
    }

    /** IPs (v4 y v6) a las que resuelve el host; si ya es una IP literal, esa misma. */
    private function resolveHostIps(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }
        // Un host sin letras que no sea IP valida (2130706433, 0x7f.1) es una IP disfrazada.
        if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || !preg_match('/[a-z]/', $host)) {
            return [];
        }

        $ips = [];
        $v4 = @gethostbynamel($host);
        if (is_array($v4)) {
            $ips = $v4;
        }
        $v6 = @dns_get_record($host, DNS_AAAA);
        if (is_array($v6)) {
            foreach ($v6 as $record) {
                if (!empty($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($ips));
    }

    /** True solo para IPs publicas (no privadas/reservadas/loopback/link-local/CGNAT ni v4 mapeadas a v6 que lo sean). */
    private function isPublicIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        $bin = @inet_pton($ip);
        if ($bin !== false && strlen($bin) === 16 && str_starts_with($bin, str_repeat("\0", 10) . "\xff\xff")) {
            return $this->isPublicIp((string) inet_ntop(substr($bin, 12)));
        }
        if ($bin !== false && strlen($bin) === 4) {
            $long = ip2long($ip);
            if ($long !== false && $long >= ip2long('100.64.0.0') && $long <= ip2long('100.127.255.255')) {
                return false; // CGNAT
            }
        }

        return true;
    }

    private function shortError(string $message): string
    {
        $message = trim(preg_replace('/\s+/', ' ', $message) ?? '');

        return mb_substr($message !== '' ? $message : 'Error desconocido', 0, 500);
    }
}
