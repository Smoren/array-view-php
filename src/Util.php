<?php

declare(strict_types=1);

namespace Smoren\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;

/**
 * Utility class containing static helper methods for array manipulation and index normalization.
 *
 * @internal
 */
class Util
{
    /**
     * Normalize a given index within the range of the container length.
     *
     * @param int $index The index to normalize.
     * @param int $containerLength The length of the container.
     * @param bool $throwError Flag to indicate if an IndexError should be thrown for out-of-range index.
     *
     * @return int The normalized index within the valid range of the container.
     *
     * @throws IndexError if the index is out of range and $throwError is true.
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
     * Check if an array is sequential (indexed from 0 to n-1).
     *
     * @param array<mixed> $source The array to check for sequential indexing.
     * @param bool $forceCustomImplementation Flag only for tests.
     *
     * @return bool Returns true if the array has sequential indexing, false otherwise.
     */
    public static function isArraySequential(array $source, bool $forceCustomImplementation = false): bool
    {
        if (!function_exists('array_is_list') || $forceCustomImplementation) {
            return \count($source) === 0 || array_keys($source) === range(0, count($source) - 1);
        }
        return array_is_list($source);
    }
}
