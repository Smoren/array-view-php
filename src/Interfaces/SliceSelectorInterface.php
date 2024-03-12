<?php

declare(strict_types=1);

namespace Smoren\ArrayView\Interfaces;

use Smoren\ArrayView\Structs\Slice;

/**
 * Interface for selecting elements from an array view by slice.
 */
interface SliceSelectorInterface extends ArraySelectorInterface
{
    /**
     * Return slice object.
     *
     * @return Slice
     */
    public function getValue(): Slice;
}
