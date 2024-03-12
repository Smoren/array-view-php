<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Structs\Slice;
use Smoren\ArrayView\Views\ArraySliceView;

/**
 * Represents a slice selector that selects elements based on the provided slice parameters.
 */
class SliceSelector extends Slice implements ArraySelectorInterface
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
     * {@inheritDoc}
     */
    public function getValue(): Slice
    {
        return $this;
    }
}
