<?php

namespace App\Services\DuplicateImages;

use App\Support\DuplicateImages\UnionFind;

/**
 * Groups physical image paths into duplicate clusters based on dHash
 * Hamming distance, using union-find to merge transitively-close pairs.
 */
class DuplicateGroupingService
{
    public function __construct(private DHasher $hasher)
    {
    }

    /**
     * @param array<string,string> $hashesByPath physicalPath => 16-hex-char hash
     * @return array<int, array<int,string>> list of groups (each a list of physical paths), singleton components excluded
     */
    public function group(array $hashesByPath, int $threshold): array
    {
        $paths = array_keys($hashesByPath);

        $unionFind = new UnionFind();
        foreach ($paths as $path) {
            $unionFind->makeSet($path);
        }

        // O(n^2) pairwise comparison: fine for a catalog of a few thousand
        // images; not optimizing further for v1 (e.g. via perceptual hash
        // bucketing/BK-trees) unless the catalog size demands it later.
        $count = count($paths);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $paths[$i];
                $b = $paths[$j];

                if ($this->hasher->hammingDistance($hashesByPath[$a], $hashesByPath[$b]) <= $threshold) {
                    $unionFind->union($a, $b);
                }
            }
        }

        $groups = [];
        foreach ($paths as $path) {
            $root = $unionFind->find($path);
            $groups[$root][] = $path;
        }

        return array_values(array_filter($groups, fn (array $group) => count($group) >= 2));
    }
}
