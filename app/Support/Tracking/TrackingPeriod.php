<?php

namespace App\Support\Tracking;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Periodo elegido en la pantalla de Google Ads (Seguimiento de conversiones): el resumen, la tabla y la exportacion
 * usan SIEMPRE el mismo, leido de la misma peticion.
 *
 * Parametros:
 *   range = today | 7 | 30 | 90 | this_month | last_month | month | custom | all   (por defecto 30)
 *   month = YYYY-MM                       (solo con range=month)
 *   from, to = YYYY-MM-DD                 (solo con range=custom; con uno solo el otro extremo queda abierto)
 *
 * Las fechas se interpretan en la zona horaria de la app (config('app.timezone'), la del panel), igual que
 * created_at. Un valor invalido nunca falla: cae en "ultimos 30 dias".
 */
class TrackingPeriod
{
    public const RANGES = ['today', '7', '30', '90', 'this_month', 'last_month', 'month', 'custom', 'all'];

    public function __construct(
        public readonly string $range,
        public readonly ?Carbon $from,
        public readonly ?Carbon $to,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $range = (string) $request->input('range', '30');
        if (!in_array($range, self::RANGES, true)) {
            $range = '30';
        }

        return self::resolve($range, $request->input('month'), $request->input('from'), $request->input('to'));
    }

    public static function resolve(string $range, mixed $month = null, mixed $from = null, mixed $to = null): self
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return new self('today', $now->copy()->startOfDay(), null);
            case '7':
                return new self('7', $now->copy()->subDays(7)->startOfDay(), null);
            case '90':
                return new self('90', $now->copy()->subDays(90)->startOfDay(), null);
            case 'all':
                return new self('all', null, null);
            case 'this_month':
                return new self('this_month', $now->copy()->startOfMonth(), $now->copy()->endOfMonth());
            case 'last_month':
                $last = $now->copy()->startOfMonth()->subMonthNoOverflow();

                return new self('last_month', $last->copy()->startOfMonth(), $last->copy()->endOfMonth());
            case 'month':
                $start = self::parseMonth($month);
                if ($start) {
                    return new self('month', $start, $start->copy()->endOfMonth());
                }
                break; // mes invalido o ausente: 30 dias
            case 'custom':
                $start = self::parseDay($from);
                $end = self::parseDay($to);
                if ($start || $end) {
                    if ($start && $end && $start->greaterThan($end)) {
                        [$start, $end] = [$end, $start];
                    }

                    return new self('custom', $start?->startOfDay(), $end?->endOfDay());
                }
                break; // sin fechas validas: 30 dias
        }

        return new self('30', $now->copy()->subDays(30)->startOfDay(), null);
    }

    /** @return array{range: string, from: ?string, to: ?string} */
    public function toArray(): array
    {
        return [
            'range' => $this->range,
            'from' => $this->from?->format('Y-m-d'),
            'to' => $this->to?->format('Y-m-d'),
        ];
    }

    private static function parseMonth(mixed $value): ?Carbon
    {
        if (!is_string($value) || preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $value, $m) !== 1) {
            return null;
        }
        $year = (int) $m[1];
        if ($year < 2000 || $year > 2100) {
            return null;
        }

        return Carbon::create($year, (int) $m[2], 1, 0, 0, 0, config('app.timezone'))->startOfMonth();
    }

    private static function parseDay(mixed $value): ?Carbon
    {
        if (!is_string($value) || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) !== 1) {
            return null;
        }
        if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1]) || (int) $m[1] < 2000 || (int) $m[1] > 2100) {
            return null;
        }

        return Carbon::create((int) $m[1], (int) $m[2], (int) $m[3], 0, 0, 0, config('app.timezone'));
    }
}
