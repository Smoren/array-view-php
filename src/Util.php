<?php

namespace Smoren\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;

class Util
{
    /**
     * @param int $index
     * @param int $containerLength
     * @param bool $throwError
     *
     * @return int
     */
    public static function normalizeIndex(int $index, int $containerLength, bool $throwError = true): int
    {
        $dist = $index >= 0 ? $index : abs($index) - 1;
        if ($throwError && $dist >= $containerLength) {
            throw new IndexError("Index {$index} is out of range.");
        }
        return $index < 0 ? $containerLength + $index : $index;
    }

    /**
     * @param array<mixed> $source
     *
     * @return bool
     */
    public static function isArraySequential(array $source): bool
    {
        if (!function_exists('array_is_list')) {
            return array_keys($source) === range(0, count($source) - 1);
        }
        return array_is_list($source);
    }
}
