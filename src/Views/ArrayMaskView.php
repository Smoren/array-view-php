<?php

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;

/**
 * @template T
 * @extends ArrayIndexListView<T>
 */
class ArrayMaskView extends ArrayIndexListView
{
    /**
     * @var array<bool>
     */
    protected array $mask;

    /**
     * @param array<T>|ArrayViewInterface<T> $source
     * @param array<bool> $mask
     * @param bool|null $readonly
     */
    public function __construct(&$source, array $mask, ?bool $readonly = null)
    {
        [$sourceSize, $maskSize] = [\count($source), \count($mask)];
        if ($sourceSize !== $maskSize) {
            throw new SizeError("Mask size not equal to source length ({$maskSize} != {$sourceSize}).");
        }

        $indexes = array_filter(
            array_map(fn (bool $v, int $i) => $v ? $i : null, $mask, array_keys($mask)),
            fn ($v) => $v !== null
        );
        parent::__construct($source, array_values($indexes), $readonly);
        $this->mask = $mask;
    }
}
