<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Structs\Slice;
use Smoren\ArrayView\Views\ArraySliceView;

/**
 * Represents a slice selector that selects elements based on the provided slice parameters.
 *
 * ##### Example
 *  ```php
 * $originalArray = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
 * $view = ArrayView::toView($originalArray);
 *
 * $selector = new SliceSelector('::2');
 * print_r($view[$selector]); // [1, 3, 5, 7, 9]
 * print_r($view->subview($selector)->toArray()); // [1, 3, 5, 7, 9]
 *
 * $selector = new SliceSelector('1::2');
 * print_r($view[$selector]); // [2, 4, 6, 8, 10]
 * print_r($view->subview($selector)->toArray()); // [2, 4, 6, 8, 10]
 *
 * $selector = new SliceSelector('-3::-2');
 * print_r($view[$selector]); // [8, 6, 4, 2]
 * print_r($view->subview($selector)->toArray()); // [8, 6, 4, 2]
 *
 * $selector = new SliceSelector('1:4');
 * print_r($view[$selector]); // [2, 3, 4]
 * print_r($view->subview($selector)->toArray()); // [2, 3, 4]
 *
 * $selector = new SliceSelector('-2:0:-1');
 * print_r($view[$selector]); // [9, 8, 7, 6, 5, 4, 3, 2]
 * print_r($view->subview($selector)->toArray()); // [9, 8, 7, 6, 5, 4, 3, 2]
 * ```
 */
final class SliceSelector extends Slice implements ArraySelectorInterface
{
    /**
     * Creates a new SliceSelector instance with the provided slice parameters.
     *
     * @param Slice|string|array<int> $slice The slice instance or slice string defining the selection.
     */
    public function __construct($slice)
    {
        $s = Slice::toSlice($slice);
        parent::__construct($s->start, $s->end, $s->step);
    }

    /**
     * Selects elements from the source array based on the slice parameters.
     *
     * @template T The type of elements in the source array.
     *
     * @param ArrayViewInterface<T> $source The source array to select elements from.
     * @param bool|null $readonly Whether the selection should be read-only.
     *
     * @return ArraySliceView<T> The view containing the selected elements.
     *
     * {@inheritDoc}
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayViewInterface
    {
        return new ArraySliceView($source, $this, $readonly ?? $source->isReadonly());
    }

    /**
     * Checks if the selector is compatible with the given view.
     *
     * @template T View elements type.
     *
     * @param ArrayViewInterface<T> $view the view to check compatibility with.
     *
     * @return bool true if the element is compatible, false otherwise
     *
     * {@inheritDoc}
     */
    public function compatibleWith(ArrayViewInterface $view): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(): Slice
    {
        return $this;
    }
}
