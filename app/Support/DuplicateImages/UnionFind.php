<?php

namespace App\Support\DuplicateImages;

/**
 * Plain, framework-agnostic union-find (disjoint-set) over arbitrary string keys,
 * with path compression and union by rank.
 */
class UnionFind
{
    /** @var array<string, string> */
    private array $parent = [];

    /** @var array<string, int> */
    private array $rank = [];

    public function makeSet(string $key): void
    {
        if (!array_key_exists($key, $this->parent)) {
            $this->parent[$key] = $key;
            $this->rank[$key] = 0;
        }
    }

    public function find(string $key): string
    {
        $this->makeSet($key);

        if ($this->parent[$key] !== $key) {
            // Path compression: point directly at the root we find recursively.
            $this->parent[$key] = $this->find($this->parent[$key]);
        }

        return $this->parent[$key];
    }

    public function union(string $a, string $b): void
    {
        $rootA = $this->find($a);
        $rootB = $this->find($b);

        if ($rootA === $rootB) {
            return;
        }

        // Union by rank: attach the shorter tree under the taller one's root.
        if ($this->rank[$rootA] < $this->rank[$rootB]) {
            [$rootA, $rootB] = [$rootB, $rootA];
        }

        $this->parent[$rootB] = $rootA;

        if ($this->rank[$rootA] === $this->rank[$rootB]) {
            $this->rank[$rootA]++;
        }
    }
}
