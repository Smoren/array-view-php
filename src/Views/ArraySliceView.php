<?php

namespace Smoren\ArrayView\Views;

use Smoren\ArrayView\Interfaces\ArrayViewInterface;
use Smoren\ArrayView\Structs\NormalizedSlice;
use Smoren\ArrayView\Structs\Slice;

/**
 * @template T
 * @extends ArrayView<T>
 */
class ArraySliceView extends ArrayView
{
    /**
     * @var NormalizedSlice
     */
    protected NormalizedSlice $slice;

    /**
     * @param Array<T>|ArrayViewInterface<T> $source
     * @param Slice $slice
     * @param bool|null $readonly
     */
    public function __construct(&$source, Slice $slice, ?bool $readonly = null)
    {
        parent::__construct($source, $readonly);
        $this->slice = $slice->normalize(\count($source));
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->slice);
    }

    /**
     * {@inheritDoc}
     */
    protected function convertIndex(int $i): int
    {
        return $this->slice->convertIndex($i);
    }
}
