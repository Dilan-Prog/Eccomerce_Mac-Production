<?php

namespace App\Services\DuplicateImages;

use App\Support\DuplicateImages\ImageReference;
use App\Support\DuplicateImages\PathNormalizer;
use App\Support\DuplicateImages\ValueFormat;
use Illuminate\Support\Collection;

/**
 * The unified image-reference abstraction: turns the 8 configured DB
 * source columns (config('duplicate_images.sources')) plus the raw
 * filesystem scan into a single collection of ImageReference objects, and
 * provides the lookup/rewrite operations the scan orchestrator and the
 * consolidation action need against a specific physical path.
 */
class ImageReferenceRegistry
{
    public function __construct(private FilesystemUploadsScanner $scanner)
    {
    }

    /**
     * Every reference across all configured sources, plus a synthetic orphan
     * ImageReference for every scanned file whose physical path isn't
     * already covered by a DB-derived reference.
     *
     * @return Collection<int, ImageReference>
     */
    public function all(): Collection
    {
        $dbRefs = collect();

        foreach (config('duplicate_images.sources', []) as $row) {
            $format = ValueFormat::from($row['format']);
            $column = $row['column'];
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
            $modelClass = $row['model'];

            $modelClass::query()
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->select(['id', $column])
                ->cursor()
                ->each(function ($model) use (&$dbRefs, $modelClass, $column, $format) {
                    $rawValue = $model->{$column};
                    $physical = PathNormalizer::toPhysical($rawValue, $format);

                    $dbRefs->push(new ImageReference(
                        $modelClass,
                        $model->id,
                        $column,
                        $rawValue,
                        $physical,
                        $format,
                    ));
                });
        }

        $seenPhysicalPaths = $dbRefs->pluck('physicalPath')->flip();

        $orphanRefs = $this->scanner->scan()
            ->reject(fn ($file) => $seenPhysicalPaths->has($file['physical_path']))
            ->map(fn ($file) => new ImageReference(
                null,
                null,
                null,
                null,
                $file['physical_path'],
                ValueFormat::BareRelative,
            ));

        return $dbRefs->merge($orphanRefs)->values();
    }

    /**
     * Every DB reference (across all configured sources — NOT the filesystem
     * scan) currently pointing at $physicalPath. Used both for "used in N
     * places" display AND the pre-delete safety check — same code path,
     * single source of truth.
     *
     * @return Collection<int, ImageReference>
     */
    public function findReferencesTo(string $physicalPath): Collection
    {
        $refs = collect();

        foreach (config('duplicate_images.sources', []) as $row) {
            $format = ValueFormat::from($row['format']);
            $column = $row['column'];
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
            $modelClass = $row['model'];

            $expectedValue = PathNormalizer::toStoredValue($physicalPath, $format);

            $matches = $modelClass::query()
                ->where($column, $expectedValue)
                ->get(['id'])
                ->map(fn ($model) => new ImageReference(
                    $modelClass,
                    $model->id,
                    $column,
                    $expectedValue,
                    $physicalPath,
                    $format,
                ));

            $refs = $refs->merge($matches);
        }

        return $refs->values();
    }

    /**
     * Batch version of findReferencesTo() for many physical paths at once —
     * one query per configured source (8 total) regardless of how many
     * paths are given, instead of one query per source PER path. Used by
     * screens that need "who else references this file" for a whole page
     * of rows (e.g. the Media Library grid's delete-confirmation warning),
     * where calling findReferencesTo() per row would be an N+1.
     *
     * @param  Collection<int, string>  $physicalPaths
     * @return Collection<string, Collection<int, ImageReference>> keyed by physical path, one entry per input path (empty collection if unreferenced)
     */
    public function findReferencesToMany(Collection $physicalPaths): Collection
    {
        $byPath = $physicalPaths->mapWithKeys(fn ($path) => [$path => collect()]);

        foreach (config('duplicate_images.sources', []) as $row) {
            $format = ValueFormat::from($row['format']);
            $column = $row['column'];
            /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
            $modelClass = $row['model'];

            $expectedValueByPath = $physicalPaths->mapWithKeys(
                fn ($path) => [$path => PathNormalizer::toStoredValue($path, $format)]
            );

            $matchesByValue = $modelClass::query()
                ->whereIn($column, $expectedValueByPath->values()->unique()->all())
                ->get(['id', $column])
                ->groupBy($column);

            foreach ($expectedValueByPath as $path => $expectedValue) {
                foreach ($matchesByValue->get($expectedValue, collect()) as $model) {
                    $byPath[$path]->push(new ImageReference(
                        $modelClass,
                        $model->id,
                        $column,
                        $expectedValue,
                        $path,
                        $format,
                    ));
                }
            }
        }

        return $byPath;
    }

    /**
     * Rewrites one specific reference's stored column value to point at
     * $newPhysicalPath, in that reference's OWN original format — never
     * changes a bare-relative column into a full-URL one or vice versa.
     * Never call this for an orphan reference — there is no DB row to
     * rewrite.
     */
    public function rewrite(ImageReference $ref, string $newPhysicalPath): void
    {
        if ($ref->isOrphan()) {
            throw new \InvalidArgumentException('Cannot rewrite an orphan ImageReference — it has no backing DB row.');
        }

        $newValue = PathNormalizer::toStoredValue($newPhysicalPath, $ref->format);

        $ref->modelClass::where('id', $ref->modelId)->update([$ref->column => $newValue]);
    }
}
