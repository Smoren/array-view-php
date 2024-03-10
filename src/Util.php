<?php

namespace Smoren\ArrayView;

use Smoren\ArrayView\Exceptions\IndexError;

class Util
{
    public static function normalizeIndex(int $index, int $containerLength, bool $throwError = true): int
    {
        $dist = $index >= 0 ? $index : abs($index) - 1;
        if ($throwError && $dist >= $containerLength) {
            throw new IndexError("Index {$index} is out of range.");
        }
        return $index < 0 ? $containerLength + $index : $index;
    }
}
