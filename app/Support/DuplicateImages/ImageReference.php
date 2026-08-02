<?php

namespace App\Support\DuplicateImages;

/**
 * One reference to an image, anywhere in the catalog: either a DB column value
 * (modelClass/modelId/column/rawValue all set) or an orphan filesystem file with
 * no corresponding DB row (modelClass/modelId/column/rawValue all null).
 */
final class ImageReference
{
    public function __construct(
        public readonly ?string $modelClass,
        public readonly ?int $modelId,
        public readonly ?string $column,
        public readonly ?string $rawValue,
        public readonly string $physicalPath,
        public readonly ValueFormat $format,
    ) {}

    public function isOrphan(): bool
    {
        return $this->modelClass === null;
    }

    /** Human-readable label for "used in N places" UI, e.g. "Producto #12 · thumb_image" or "Archivo huérfano". */
    public function label(): string
    {
        if ($this->isOrphan()) {
            return 'Archivo huérfano';
        }
        $short = class_basename($this->modelClass);
        return "{$short} #{$this->modelId} · {$this->column}";
    }
}
