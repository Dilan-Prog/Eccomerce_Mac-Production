<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Throwable;

class MarketingApiToken extends Model
{
    use HasFactory;

    /** Uso: rutas /api/marketing/* (email marketing). Es el default: los tokens previos siguen aqui. */
    public const SCOPE_MARKETING = 'marketing';

    /** Uso: GET /api/attribution/lookup (n8n / Google Ads). */
    public const SCOPE_ATTRIBUTION = 'attribution';

    protected $hidden = ['secret_hash'];

    protected $casts = [
        'last_used_at' => 'datetime',
        'status' => 'boolean',
    ];

    /** Usos validos => etiqueta para el panel. */
    public static function useLabels(): array
    {
        return [
            self::SCOPE_MARKETING => 'Email Marketing',
            self::SCOPE_ATTRIBUTION => 'Atribución Google Ads',
        ];
    }

    /** Etiqueta del uso de este token; una fila sin scope (antes de migrar) cuenta como marketing. */
    public function useLabel(): string
    {
        return self::useLabels()[$this->scope ?: self::SCOPE_MARKETING] ?? (string) $this->scope;
    }

    /** Filtra por uso: MarketingApiToken::forUse('attribution')->where('status', 1)... */
    public function scopeForUse(Builder $query, string $use): Builder
    {
        return $query->where('scope', $use);
    }

    /**
     * Cuantos tokens ACTIVOS de uso "attribution" hay. Solo un conteo: jamas valores.
     * Tolera que la tabla o la columna `scope` aun no existan (deploy antes de migrate): devuelve 0.
     */
    public static function activeAttributionCount(): int
    {
        try {
            return (int) static::query()->forUse(self::SCOPE_ATTRIBUTION)->where('status', 1)->count();
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Busca el token activo de un uso a partir del bearer "{key_id}.{secret}" y valida el secreto
     * contra su hash. Devuelve el registro o null (formato invalido, key_id inexistente, revocado,
     * de otro uso o secreto incorrecto). NO actualiza last_used_at: lo hace quien autentica.
     *
     * El uso se compara en PHP (no en el WHERE) a proposito: asi, si la columna `scope` aun no existe
     * (codigo desplegado antes de `php artisan migrate`), el atributo viene vacio y el token cuenta
     * como "marketing" (el flujo de email marketing no se rompe) y nunca como "attribution". Funciona
     * igual en cualquier motor de BD. key_id es unico, asi que es a lo mas una fila.
     */
    public static function findActiveByBearer(string $bearer, string $use): ?self
    {
        if (!str_contains($bearer, '.')) {
            return null;
        }

        [$keyId, $secret] = explode('.', $bearer, 2);
        if ($keyId === '' || $secret === '') {
            return null;
        }

        try {
            $record = static::query()->where('key_id', $keyId)->where('status', 1)->first();
        } catch (Throwable) {
            return null;
        }

        if (!$record || ($record->scope ?: self::SCOPE_MARKETING) !== $use) {
            return null;
        }

        try {
            if (!Hash::check($secret, (string) $record->secret_hash)) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }

        return $record;
    }

    /** Marca el ultimo uso sin dejar que un fallo de escritura rompa la peticion ya autenticada. */
    public function touchLastUsed(): void
    {
        try {
            $this->last_used_at = now();
            $this->save();
        } catch (Throwable) {
            // No critico: el token ya se valido.
        }
    }
}
