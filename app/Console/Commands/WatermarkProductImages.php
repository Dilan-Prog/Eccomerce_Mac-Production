<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\UploadPath;
use App\Traits\ImageUploadTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Gd\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class WatermarkProductImages extends Command
{
    use ImageUploadTrait;

    /**
     * php artisan product-images:watermark
     * php artisan product-images:watermark --dry-run
     * php artisan product-images:watermark --limit=5
     * php artisan product-images:watermark --reset
     * php artisan product-images:watermark --skus=ABC-123,DEF-456
     * php artisan product-images:watermark --sku-file=/ruta/a/skus.txt (uno por línea)
     */
    protected $signature = 'product-images:watermark {--dry-run} {--limit=0} {--reset} {--skus=} {--sku-file=}';

    protected $description = 'Aplica la marca de agua configurada (config/watermark.php) al catálogo de imágenes de producto existente, sobrescribiendo los archivos en su misma ruta';

    /**
     * Registro de rutas ya procesadas (una por línea), para poder reanudar sin
     * volver a marcar imágenes que ya quedaron con watermark en una corrida
     * anterior interrumpida (timeout, memoria, etc.).
     */
    private string $stateFile = 'watermark-processed.log';

    public function handle()
    {
        if (!config('watermark.enabled')) {
            $this->error('Marca de agua deshabilitada en config/watermark.php (WATERMARK_ENABLED=false). Nada que hacer.');
            return self::FAILURE;
        }

        if ($this->option('reset')) {
            Storage::disk('local')->delete($this->stateFile);
            $this->info('Registro de progreso borrado. La próxima corrida procesará todo desde cero.');
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $alreadyDone = $this->loadProcessed();

        $skus = $this->collectSkus();
        if ($skus === null) {
            // --sku-file apuntaba a un archivo inválido/vacío; collectSkus ya reportó el error.
            return self::FAILURE;
        }

        $query = Product::with('productImageGalleries');
        if ($skus !== []) {
            $query->whereIn('sku', $skus);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }
        $products = $query->get();

        if ($skus !== []) {
            $foundSkus = $products->pluck('sku')->all();
            $missing = array_diff($skus, $foundSkus);
            foreach ($missing as $missingSku) {
                $this->warn("SKU no encontrado en la base de datos: {$missingSku}");
            }
        }

        $manager = new ImageManager(new Driver());
        $processed = 0;
        $skipped = 0;
        $alreadyDoneCount = 0;

        foreach ($products as $product) {
            $paths = [$product->thumb_image];
            foreach ($product->productImageGalleries as $gallery) {
                $paths[] = $gallery->image;
            }

            foreach (array_filter($paths) as $imageRef) {
                $localPath = $this->resolveLocalPath($imageRef);

                if (!$localPath || !File::exists($localPath)) {
                    $this->warn("Omitido (no existe): {$imageRef}");
                    $skipped++;
                    continue;
                }

                if (isset($alreadyDone[$localPath])) {
                    $alreadyDoneCount++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] Se marcaría: {$localPath}");
                    $processed++;
                    continue;
                }

                $img = $manager->read($localPath);
                $img = $this->applyWatermark($img);
                $img->encode(new WebpEncoder(quality: 75))->save($localPath);
                $this->markProcessed($localPath);

                $this->info("Marcado: {$localPath}");
                $processed++;
            }
        }

        $verb = $dryRun ? 'se marcarían' : 'marcadas';
        $this->info("Listo. {$processed} imágenes {$verb}, {$skipped} omitidas por no encontrarse, {$alreadyDoneCount} ya estaban marcadas de una corrida anterior.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, bool> rutas absolutas ya marcadas, como llaves para
     * búsqueda O(1).
     */
    private function loadProcessed(): array
    {
        if (!Storage::disk('local')->exists($this->stateFile)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', Storage::disk('local')->get($this->stateFile));

        return array_fill_keys(array_filter($lines), true);
    }

    private function markProcessed(string $localPath): void
    {
        Storage::disk('local')->append($this->stateFile, $localPath);
    }

    /**
     * Combina --skus (lista separada por comas) y --sku-file (uno por línea).
     * Devuelve [] si no se pasó ningún filtro (procesar todo el catálogo), o
     * null si --sku-file se indicó pero no se pudo leer.
     *
     * @return array<int, string>|null
     */
    private function collectSkus(): ?array
    {
        $skus = [];

        $inline = (string) $this->option('skus');
        if ($inline !== '') {
            $skus = array_merge($skus, array_map('trim', explode(',', $inline)));
        }

        $file = (string) $this->option('sku-file');
        if ($file !== '') {
            if (!File::exists($file)) {
                $this->error("--sku-file no existe: {$file}");
                return null;
            }
            $lines = preg_split('/\r\n|\r|\n/', File::get($file));
            $skus = array_merge($skus, array_map('trim', $lines));
        }

        return array_values(array_unique(array_filter($skus)));
    }

    /**
     * thumb_image se guarda como URL absoluta (a veces con el dominio de producción
     * aunque se corra en local/staging); las imágenes de galería, como ruta relativa
     * a UploadPath::base(). Se descarta el esquema+host de cualquier URL absoluta en
     * vez de compararla contra config('app.url'), para no fallar con datos de
     * producción copiados a un entorno con otro dominio.
     */
    private function resolveLocalPath(string $imageRef): ?string
    {
        if (preg_match('#^https?://#i', $imageRef)) {
            $relative = ltrim(parse_url($imageRef, PHP_URL_PATH) ?? '', '/');
        } else {
            $relative = ltrim($imageRef, '/');
        }

        return $relative ? UploadPath::full($relative) : null;
    }
}
