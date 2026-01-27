<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAspelQty extends Command
{
    protected $signature = 'app:sync-aspel-qty
                            {--dry-run : No escribe, solo muestra conteos}
                            {--batch=500 : Tamaño del lote (100, 500, 1000...)}';

    protected $description = 'Sincroniza qty_aspel desde aspel_products.exist (por lotes)';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $batchSize = (int) $this->option('batch');
        if ($batchSize <= 0) $batchSize = 100;

        $totalCandidates = 0;
        $totalUpdated = 0;

        DB::table('products as p')
            ->join('aspel_products as ap', 'ap.cve_art', '=', 'p.sku')
            ->select('p.id as id', 'p.qty_aspel', 'ap.exist')
            ->orderBy('p.id')
            ->chunk($batchSize, function ($rows) use ($dryRun, &$totalCandidates, &$totalUpdated) {

                $updates = $rows->filter(function ($row) {
                    $current = (int) ($row->qty_aspel ?? 0);
                    $aspelExist = (int) ($row->exist ?? 0);
                    return $current !== $aspelExist;
                })->map(function ($row) {
                    $newQty = (int) ($row->exist ?? 0);
                    if ($newQty < 0) $newQty = 0;

                    return [
                        'id' => (int) $row->id,
                        'new_qty_aspel' => $newQty,
                    ];
                })->values();

                $totalCandidates += $updates->count();

                if ($updates->isEmpty()) {
                    return;
                }

                if ($dryRun) {
                    return;
                }

                $ids = $updates->pluck('id')->all();

                $caseSql = "CASE id ";
                foreach ($updates as $u) {
                    $caseSql .= "WHEN {$u['id']} THEN {$u['new_qty_aspel']} ";
                }
                $caseSql .= "END";

                DB::table('products')
                    ->whereIn('id', $ids)
                    ->update(['qty_aspel' => DB::raw($caseSql)]);

                $totalUpdated += count($ids);
            });

        if ($dryRun) {
            $this->info("🧪 Dry run: {$totalCandidates} registros candidatos (por lotes).");
            return self::SUCCESS;
        }

        Log::info('app:sync-aspel-qty actualizado', [
            'candidates' => $totalCandidates,
            'updated' => $totalUpdated,
            'batch' => $batchSize,
        ]);

        $this->info("✔ Terminado. Candidatos: {$totalCandidates}. Actualizados: {$totalUpdated}. Lote: {$batchSize}");

        return self::SUCCESS;
    }
}
