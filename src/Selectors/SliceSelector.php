<?php

namespace Smoren\ArrayView\Selectors;

use Smoren\ArrayView\Interfaces\ArraySelectorInterface;
use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Structs\Slice;
use Smoren\ArrayView\Views\ArraySliceView;

class SliceSelector extends Slice implements ArraySelectorInterface
{
    /**
     * @param Slice|string $slice
     */
    public function __construct($slice)
    {
        $s = Slice::toSlice($slice);
        parent::__construct($s->start, $s->end, $s->step);
    }

    /**
     * @template T
     *
     * @param ArrayViewInterface<T> $source
     * @param bool|null $readonly
     *
     * @return ArraySliceView<T>
     *
     * {@inheritDoc}
     */
    public function select(ArrayViewInterface $source, ?bool $readonly = null): ArrayViewInterface
    {
        return new ArraySliceView($source, $this, $readonly ?? $source->isReadonly());
    }
}
