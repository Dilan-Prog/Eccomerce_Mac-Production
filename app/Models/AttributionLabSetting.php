<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Ajustes del Laboratorio de atribucion guardados en BD (clave/valor).
 *
 * Claves en uso: 'test_whatsapp_number' y 'n8n_test_url'. Sin cache persistente: cada lectura va a la BD
 * (solo la usan el panel y el modo prueba, no el trafico normal del sitio).
 */
class AttributionLabSetting extends Model
{
    public const KEY_TEST_WHATSAPP = 'test_whatsapp_number';
    public const KEY_N8N_URL = 'n8n_test_url';

    protected $table = 'attribution_lab_settings';

    protected $fillable = ['key', 'value'];

    /**
     * Valor guardado, o $default si no hay fila / esta vacio / la tabla aun no existe (deploy antes
     * de `php artisan migrate`): nunca lanza.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            $value = static::query()->where('key', $key)->value('value');
        } catch (\Throwable $e) {
            return $default;
        }

        return ($value === null || $value === '') ? $default : (string) $value;
    }

    /**
     * Guarda $value; null o '' borra la fila. Si la tabla no existe falla con un mensaje claro.
     *
     * @throws RuntimeException
     */
    public static function put(string $key, ?string $value): void
    {
        try {
            if ($value === null || $value === '') {
                static::query()->where('key', $key)->delete();

                return;
            }

            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'No se pudo guardar el ajuste: falta la tabla attribution_lab_settings. Ejecuta "php artisan migrate" y vuelve a intentarlo.',
                0,
                $e
            );
        }
    }
}
