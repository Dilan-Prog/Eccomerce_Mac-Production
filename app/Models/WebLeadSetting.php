<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Ajustes de "Solicitudes web" guardados en BD (clave/valor), editables desde el panel.
 *
 * Clave en uso: 'notify_to' (correos que reciben el aviso, separados por comas).
 */
class WebLeadSetting extends Model
{
    public const KEY_NOTIFY_TO = 'notify_to';

    protected $table = 'web_lead_settings';

    protected $fillable = ['key', 'value'];

    /**
     * Valor guardado, o $default si no hay fila / esta vacio / la tabla aun no existe (deploy antes de
     * `php artisan migrate`): nunca lanza, para que el envio de solicitudes no dependa de esta tabla.
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
                'No se pudo guardar el ajuste: falta la tabla web_lead_settings. Ejecuta "php artisan migrate" y vuelve a intentarlo.',
                0,
                $e
            );
        }
    }
}
