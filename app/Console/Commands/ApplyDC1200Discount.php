<?php

namespace App\Console\Commands;
use App\Models\Product;
use App\Models\Category;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyDC1200Discount extends Command
{
    protected $signature = 'products:discount-dc1200 {--dry-run}';
    protected $description = 'Sincroniza qty_aspel desde aspel_products.exist';

     public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        $updated = 0;
        $skippedPersonalized = 0;
        $skippedInactive = 0;
        $skippedNullExist = 0;

        DB::table('products as p')
            ->join('aspel_products as ap', 'ap.sku', '=', 'p.sku')
            ->select('p.id', 'p.qty_personalizated', 'p.status', 'ap.exist')
            ->orderBy('p.id')
            ->chunkById(1000, function ($rows) use ($dryRun, &$updated, &$skippedPersonalized, &$skippedInactive, &$skippedNullExist) {
                $updates = [];

                foreach ($rows as $row) {
                    // VALIDACIONES (ajústalas a tu regla real)
                    if ((int)$row->status === 0) {
                        $skippedInactive++;
                        continue;
                    }

                    if ((int)$row->qty_personalizated === 1) {
                        $skippedPersonalized++;
                        continue;
                    }

                    if ($row->exist === null) {
                        $skippedNullExist++;
                        continue;
                    }

                    $newQtyAspel = (int) $row->exist;
                    if ($newQtyAspel < 0) $newQtyAspel = 0;

                    $updates[] = ['id' => $row->id, 'qty_aspel' => $newQtyAspel];
                }

                if (empty($updates)) {
                    return;
                }

                if ($dryRun) {
                    $updated += count($updates);
                    return;
                }

                // Update masivo (CASE WHEN) para evitar 1 query por fila
                $ids = array_column($updates, 'id');

                $case = "CASE id ";
                $bindings = [];
                foreach ($updates as $u) {
                    $case .= "WHEN ? THEN ? ";
                    $bindings[] = $u['id'];
                    $bindings[] = $u['qty_aspel'];
                }
                $case .= "END";

                DB::table('products')
                    ->whereIn('id', $ids)
                    ->update(['qty_aspel' => DB::raw($case)]);

                $updated += count($updates);
            });

        $this->info($dryRun
            ? "🧪 Dry-run: se actualizarían {$updated} productos."
            : "✔ Stock Aspel sincronizado correctamente. Actualizados: {$updated}"
        );

        $this->line("Saltados por status=0: {$skippedInactive}");
        $this->line("Saltados por qty_personalizated=1: {$skippedPersonalized}");
        $this->line("Saltados por exist=null: {$skippedNullExist}");

        return self::SUCCESS;
    }
}
