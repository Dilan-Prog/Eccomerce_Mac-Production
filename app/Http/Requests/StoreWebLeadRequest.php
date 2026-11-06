<?php

namespace App\Http\Requests;

use App\Models\WebLead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validacion del envio de los formularios publicos (asociados, calibracion y puesta en marcha,
 * medicion, cotizacion publica). Ver WebLeadController.
 *
 * Los datos de origen/atribucion NUNCA rechazan la solicitud: se recortan al largo de la columna.
 * Anti-spam (no son reglas de validacion, ver isBot()):
 *  - honeypot `website`: campo oculto que una persona no llena.
 *  - `form_started_at`: epoch (segundos) del servidor cuando se pinto el formulario; si el envio llega
 *    antes de config('webleads.min_fill_seconds') es un robot. Si falta o no es numerico NO se bloquea.
 */
class StoreWebLeadRequest extends FormRequest
{
    /** Largo maximo (= columna de web_leads) de los campos de origen/atribucion que se recortan. */
    public const TRUNCATE_LIMITS = [
        'source_page' => 500,
        'landing_page' => 500,
        'referrer' => 500,
        'session_id' => 64,
        'gclid' => 255,
        'gbraid' => 255,
        'wbraid' => 255,
        'fbclid' => 255,
        'utm_source' => 190,
        'utm_medium' => 190,
        'utm_campaign' => 190,
        'utm_term' => 190,
        'utm_content' => 190,
    ];

    /** Campos del formulario (se validan y rechazan si exceden el largo). */
    public const FORM_FIELDS = [
        'first_name', 'last_name', 'email', 'phone', 'company', 'city', 'state', 'operation', 'message',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Los campos de atribucion se recortan ANTES de validar para que un valor largo (p. ej. un referrer
     * enorme) o un tipo raro (arreglo) nunca haga fallar el envio de un cliente real.
     */
    protected function prepareForValidation(): void
    {
        $clean = [];
        foreach (self::TRUNCATE_LIMITS as $field => $max) {
            $value = $this->input($field);
            $clean[$field] = is_scalar($value) ? $this->cut((string) $value, $max) : null;
        }

        $this->merge($clean);
    }

    public function rules(): array
    {
        $rules = [
            'form_type' => ['required', 'string', Rule::in(array_keys(WebLead::FORM_TYPES))],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'operation' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];

        foreach (self::TRUNCATE_LIMITS as $field => $max) {
            $rules[$field] = ['nullable', 'string'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'form_type.required' => 'No se pudo identificar el formulario. Recarga la página e inténtalo de nuevo.',
            'form_type.in' => 'El tipo de formulario no es válido. Recarga la página e inténtalo de nuevo.',
            'first_name.required' => 'Escribe tu nombre.',
            'first_name.max' => 'El nombre no puede tener más de :max caracteres.',
            'last_name.max' => 'El apellido no puede tener más de :max caracteres.',
            'email.required' => 'Escribe tu correo electrónico.',
            'email.email' => 'Escribe un correo electrónico válido.',
            'email.max' => 'El correo no puede tener más de :max caracteres.',
            'phone.max' => 'El teléfono no puede tener más de :max caracteres.',
            'company.max' => 'La empresa no puede tener más de :max caracteres.',
            'city.max' => 'La ciudad no puede tener más de :max caracteres.',
            'state.max' => 'El estado no puede tener más de :max caracteres.',
            'operation.max' => 'La operación no puede tener más de :max caracteres.',
            'message.max' => 'El mensaje no puede tener más de :max caracteres.',
            '*.string' => 'El valor enviado no es válido.',
        ];
    }

    /**
     * True si el envio parece de un robot (honeypot lleno o enviado demasiado rapido).
     * El controlador responde igual que en un exito pero no guarda ni avisa.
     */
    public function isBot(): bool
    {
        $honeypot = $this->input('website');
        if (is_array($honeypot) || (is_scalar($honeypot) && trim((string) $honeypot) !== '')) {
            return true;
        }

        $startedAt = $this->input('form_started_at');
        if (is_scalar($startedAt) && is_numeric($startedAt) && (float) $startedAt > 0) {
            $elapsed = now()->getTimestamp() - (float) $startedAt;
            if ($elapsed < (int) config('webleads.min_fill_seconds', 3)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Datos listos para WebLead::create(): solo campos permitidos, recortados, '' => null.
     */
    public function leadData(): array
    {
        $limits = self::TRUNCATE_LIMITS + [
            'first_name' => 120, 'last_name' => 120, 'email' => 190, 'phone' => 40, 'company' => 190,
            'city' => 120, 'state' => 120, 'operation' => 190, 'message' => 5000,
        ];

        $data = ['form_type' => (string) $this->input('form_type')];
        foreach ($limits as $field => $max) {
            $value = $this->input($field);
            $value = is_scalar($value) ? $this->cut((string) $value, $max) : null;
            $data[$field] = ($value === null || $value === '') ? null : $value;
        }

        $data['status'] = 'nuevo';

        return $data;
    }

    private function cut(string $value, int $max): string
    {
        return mb_substr(trim($value), 0, $max);
    }
}
