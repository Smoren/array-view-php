<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Exceptions\ReadonlyError;
use Smoren\ArrayView\Exceptions\SizeError;
use Smoren\ArrayView\Exceptions\ValueError;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;

/**
 * Class representing a mask-based view of an array or another ArrayView for accessing elements based on a boolean mask.
 *
 * Each element in the view is included or excluded based on the specified boolean mask.
 *
 * @template T
 *
 * <code>
 * <?php
 *
 * $source = [1, 2, 3, 4, 5];
 * $view = ArrayView::toView($source)->subview(new MaskSelector([true, false, true, false, true]));
 * $view->toArray(); // [1, 3, 5]
 *
 * </code>
 *
 * @extends ArrayIndexListView<T>
 */
class ArrayMaskView extends ArrayIndexListView
{
    /**
     * @var array<bool> The boolean mask specifying whether each element in the source array
     * should be included in the view (true) or excluded (false).
     */
    protected array $mask;

    /**
     * Constructs a new ArrayMaskView instance with the specified source array or ArrayView and boolean mask.
     *
     * @param array<T>|ArrayViewInterface<T> $source The source array or ArrayView to create a view from.
     * @param array<bool> $mask Options for configuring the view.
     * @param bool|null $readonly The boolean mask for including or excluding elements from the source array.
     *
     * @throws ValueError if the array is not sequential.
     * @throws ReadonlyError if the source is readonly and trying to create a non-readonly view.
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
