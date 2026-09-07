<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'min_cost',
        'cost',
        'status',
        'delivery_days_min',
        'delivery_days_max',
        'delivery_note',
    ];

    /**
     * Texto de entrega que ve el cliente, redactado a partir del rango de días.
     *
     * Es la ÚNICA fuente de ese texto: el checkout lo pide aquí en vez de
     * escribirlo, para que el día que aparezca en otra pantalla no haya dos
     * redacciones distintas que mantener.
     *
     * Nunca dice "hábiles" — el negocio pidió expresamente quitar esa palabra,
     * porque alargaba la expectativa de entrega y costaba ventas.
     *
     * Devuelve cadena vacía si la regla no tiene tiempo definido, para que la
     * vista pueda omitir el renglón en vez de mostrar un hueco.
     */
    public function deliveryLabel(): string
    {
        $min = $this->delivery_days_min;
        $max = $this->delivery_days_max;
        $nota = trim((string) $this->delivery_note);

        if ($min === null && $max === null) {
            return $nota;
        }

        // Con un solo extremo capturado se asume que el otro es igual: un
        // "1 día" a medio llenar debe leerse bien, no quedarse en blanco.
        $min = $min ?? $max;
        $max = $max ?? $min;

        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        if ($min === $max) {
            $tiempo = match ((int) $min) {
                0 => 'Mismo día',
                1 => 'Siguiente día',
                default => $min . ' días',
            };
        } else {
            $tiempo = $min . '–' . $max . ' días';
        }

        return $nota !== '' ? $nota . ' · ' . $tiempo : $tiempo;
    }
}
