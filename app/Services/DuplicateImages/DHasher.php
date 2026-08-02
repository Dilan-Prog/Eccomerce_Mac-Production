<?php

namespace App\Services\DuplicateImages;

use App\Models\ImageHashCache;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Cache-aware 64-bit dHash (difference hash) computation, backed by the
 * image_hash_cache table so re-scans skip files whose size/mtime haven't
 * changed since they were last hashed.
 */
class DHasher
{
    /** @var array<int,int>|null lazily-built 256-entry popcount lookup table */
    private static ?array $popcountTable = null;

    /**
     * Returns a 16-hex-char dHash for the file at $physicalPath, using/populating
     * the image_hash_cache table. Returns null if the file doesn't exist or
     * can't be read as an image.
     */
    public function hash(string $physicalPath): ?string
    {
        if (!is_file($physicalPath) || !is_readable($physicalPath)) {
            return null;
        }

        $pathHash = md5($physicalPath);
        $algoVersion = (int) config('duplicate_images.hash_algo_version', 1);

        $fileSize = @filesize($physicalPath);
        $fileMtime = @filemtime($physicalPath);

        if ($fileSize === false || $fileMtime === false) {
            return null;
        }

        $cached = ImageHashCache::query()->where('path_hash', $pathHash)->first();

        if (
            $cached
            && (int) $cached->file_size === (int) $fileSize
            && (int) $cached->file_mtime === (int) $fileMtime
            && (int) $cached->hash_algo_version === $algoVersion
        ) {
            return $cached->image_hash;
        }

        $hex = $this->computeHash($physicalPath);

        if ($hex === null) {
            return null;
        }

        ImageHashCache::query()->updateOrCreate(
            ['path_hash' => $pathHash],
            [
                'physical_path' => $physicalPath,
                'file_size' => $fileSize,
                'file_mtime' => $fileMtime,
                'image_hash' => $hex,
                'hash_algo_version' => $algoVersion,
                'last_hashed_at' => now(),
            ]
        );

        return $hex;
    }

    /** Hamming distance between two 16-hex-char hashes (0-64). */
    public function hammingDistance(string $hexA, string $hexB): int
    {
        if (!$this->isValidHex($hexA) || !$this->isValidHex($hexB)) {
            return 64;
        }

        $binA = hex2bin($hexA);
        $binB = hex2bin($hexB);

        if ($binA === false || $binB === false || strlen($binA) !== 8 || strlen($binB) !== 8) {
            return 64;
        }

        $xor = $binA ^ $binB;
        $table = self::popcountTable();

        $distance = 0;
        for ($i = 0; $i < 8; $i++) {
            $distance += $table[ord($xor[$i])];
        }

        return $distance;
    }

    private function isValidHex(string $hex): bool
    {
        return strlen($hex) === 16 && ctype_xdigit($hex);
    }

    /**
     * Computes the 64-bit dHash for the given file, returning a 16-hex-char
     * string, or null if the image can't be decoded.
     */
    private function computeHash(string $physicalPath): ?string
    {
        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($physicalPath)->greyscale()->resize(9, 8);

            $hi = 0;
            $lo = 0;
            $bitIndex = 0;

            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $left = $img->pickColor($x, $y)->toArray()[0];
                    $right = $img->pickColor($x + 1, $y)->toArray()[0];
                    $bit = $left > $right ? 1 : 0;

                    if ($bitIndex < 32) {
                        $hi = (($hi << 1) | $bit) & 0xFFFFFFFF;
                    } else {
                        $lo = (($lo << 1) | $bit) & 0xFFFFFFFF;
                    }

                    $bitIndex++;
                }
            }

            $binary = pack('N2', $hi, $lo);

            return bin2hex($binary);
        } catch (Throwable $e) {
            return null;
        }
    }

    /** @return array<int,int> */
    private static function popcountTable(): array
    {
        if (self::$popcountTable !== null) {
            return self::$popcountTable;
        }

        $table = [];
        for ($n = 0; $n < 256; $n++) {
            $count = 0;
            $v = $n;
            while ($v > 0) {
                $count += $v & 1;
                $v >>= 1;
            }
            $table[$n] = $count;
        }

        return self::$popcountTable = $table;
    }
}
