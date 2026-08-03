<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Refreshes products.aspel_price/aspel_offert_price from the price tier
 * (aspel_price_tier/aspel_offert_price_tier, a precio_x_product_aspel.cve_precio)
 * the admin picked when they last saved the product — mirrors SyncAspelQty's
 * "recompute from Aspel's current data" approach, but for price instead of
 * stock, since only qty had a scheduled refresh before this.
 *
 * php artisan app:sync-aspel-prices
 * php artisan app:sync-aspel-prices --dry-run
 * php artisan app:sync-aspel-prices --batch=1000
 */
class SyncAspelPrices extends Command
{
    protected $signature = 'app:sync-aspel-prices
                            {--dry-run : No escribe, solo muestra conteos}
                            {--batch=500 : Tamaño del lote (100, 500, 1000...)}';

    protected $description = 'Recalcula aspel_price/aspel_offert_price desde el nivel de precio guardado por producto (por lotes)';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $batchSize = (int) $this->option('batch');
        if ($batchSize <= 0) $batchSize = 100;

        $ivaValue = (float) (DB::table('general_settings')->value('iva_mexico') ?? 16.00);

        $totals = [
            'aspel_price' => $this->syncColumn('aspel_price', 'aspel_price_tier', $ivaValue, $batchSize, $dryRun),
            'aspel_offert_price' => $this->syncColumn('aspel_offert_price', 'aspel_offert_price_tier', $ivaValue, $batchSize, $dryRun),
        ];

        if ($dryRun) {
            $this->info(sprintf(
                "🧪 Dry run: %d candidatos en aspel_price, %d candidatos en aspel_offert_price.",
                $totals['aspel_price']['candidates'],
                $totals['aspel_offert_price']['candidates']
            ));
            return self::SUCCESS;
        }

        Log::info('app:sync-aspel-prices actualizado', [
            'batch' => $batchSize,
            'iva' => $ivaValue,
            'aspel_price' => $totals['aspel_price'],
            'aspel_offert_price' => $totals['aspel_offert_price'],
        ]);

        $this->info(sprintf(
            "✔ Terminado. aspel_price: %d/%d actualizados. aspel_offert_price: %d/%d actualizados. Lote: %d",
            $totals['aspel_price']['updated'],
            $totals['aspel_price']['candidates'],
            $totals['aspel_offert_price']['updated'],
            $totals['aspel_offert_price']['candidates'],
            $batchSize
        ));

        return self::SUCCESS;
    }

    /**
     * @return array{candidates:int, updated:int}
     */
    private function syncColumn(string $priceColumn, string $tierColumn, float $ivaValue, int $batchSize, bool $dryRun): array
    {
        $totalCandidates = 0;
        $totalUpdated = 0;

        DB::table('products as p')
            ->join('precio_x_product_aspel as ppa', function ($join) use ($tierColumn) {
                $join->on('ppa.cve_art', '=', 'p.sku')->on('ppa.cve_precio', '=', "p.{$tierColumn}");
            })
            ->join('aspel_products as ap', 'ap.cve_art', '=', 'p.sku')
            ->leftJoin('monedas_aspel as m', 'm.num_moneda', '=', 'ap.num_mon')
            ->whereNotNull("p.{$tierColumn}")
            ->select('p.id as id', "p.{$priceColumn} as current_price", 'ppa.precio as raw', 'm.cve_moned', 'm.tipo_cambio')
            ->orderBy('p.id')
            ->chunk($batchSize, function ($rows) use ($priceColumn, $ivaValue, $dryRun, &$totalCandidates, &$totalUpdated) {
                $updates = $rows->map(function ($row) use ($ivaValue) {
                    $isMxn = !$row->cve_moned || $row->cve_moned === 'MXN';
                    $exchangeRate = $isMxn ? 1.0 : (float) $row->tipo_cambio;
                    $converted = (float) $row->raw * $exchangeRate;
                    $newPrice = round($converted * (1 + $ivaValue / 100), 2);

                    return [
                        'id' => (int) $row->id,
                        'current' => $row->current_price !== null ? round((float) $row->current_price, 2) : null,
                        'new' => $newPrice,
                    ];
                })->filter(fn ($u) => $u['current'] !== $u['new'])->values();

                $totalCandidates += $updates->count();

                if ($updates->isEmpty() || $dryRun) {
                    return;
                }

                $ids = $updates->pluck('id')->all();

                $caseSql = "CASE id ";
                foreach ($updates as $u) {
                    $caseSql .= "WHEN {$u['id']} THEN {$u['new']} ";
                }
                $caseSql .= "END";

                DB::table('products')
                    ->whereIn('id', $ids)
                    ->update([$priceColumn => DB::raw($caseSql)]);

                $totalUpdated += count($ids);
            });

        return ['candidates' => $totalCandidates, 'updated' => $totalUpdated];
    }
}
